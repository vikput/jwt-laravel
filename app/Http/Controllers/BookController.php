<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\UserBook;
use JWTAuth;
use Validator;

class BookController extends Controller
{
	private $book;
	private $userBook;
    public function __construct(Book $book, UserBook $userBook) {
        $this->book = $book;
        $this->userBook = $userBook;
    }

    public function addBook(Request $request) {
        $response = [];

        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);

        if ($user) {
        	$validator = Validator::make($request->all(), [
                'book_name' => 'required',
                'author' => 'required|max:20',
                'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
	        ]);

	        if ($validator->fails()) {
	            $response['errors']['status'] = 400;
	            $response['errors']['messages'] = $validator->errors()->all();
	            return response()->json($response);
	        }

	        $book = $this->book->create([
	          'book_name' => $request->get('book_name'),
	          'author' => $request->get('author'),
	        ]);
            
            if ($book) {
                
                if ($request->hasFile('cover_image')) {
			        $image = $request->file('cover_image');
			        $name = str_replace(' ', '_', $request->get('book_name'));
			        $name = $name.'.'.$image->getClientOriginalExtension();
			        $destinationPath = public_path('/cover_images');
			        $image->move($destinationPath, $name);
			        
			        //Update cover_image name in table.
			        $this->book->updateCoverName($book, $name);
			    }

            	$response['success']['status'] = 200;
                $response['success']['message'] = 'Book created successfully';
            } else {
            	$response['errors']['status'] = 500;
                $response['errors']['message'] = 'Whoops, something went wrong.';
            }

        } else {
            $response['errors']['status'] = 500;
            $response['errors']['message'] = 'Whoops, something went wrong.';
        }
        return response()->json($response);
    }

    public function getAllBooks(Request $request) {
        $response = [];
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        if ($user) {
        	$booksList = $this->book->fetchAll();
            $data = [];
            if ($booksList) {
                foreach ($booksList as $key => $value) {
                	$data[] = array(
                        'book_name' => $value->book_name,
                        'author' => $value->author,
                        'cover_image' => url('/cover_images'.'/'.$value->cover_image)
                	);
                }
                $response['success']['status'] = 200;
                $response['success']['data'] = $data;
            } else {
                $response['errors']['status'] = 500;
                $response['errors']['message'] = 'No data available.';
            }
        } else {
            $response['errors']['status'] = 500;
            $response['errors']['message'] = 'No data available.';
        }
        return response()->json($response);
    }

    public function getBookDetails(Request $request, $book_id) {
        $response = [];
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        if ($user) {
            $bookDetails = $this->book->getBookById($book_id);
            if ($bookDetails) {
                $data['book_name'] = $bookDetails->book_name;
                $data['author'] = $bookDetails->author;
                $data['cover_image'] = url('/cover_images'.'/'.$bookDetails->cover_image);

                $response['success']['status'] = 200;
                $response['success']['data'] = $data;	
            } else {
                $response['errors']['status'] = 500;
                $response['errors']['message'] = 'No data available.';
            }
        } else {
        	$response['errors']['status'] = 500;
            $response['errors']['message'] = 'No data available.';
        }
        return response()->json($response);
    }

    public function rentBook(Request $request) {
    	$response = [];
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        if ($user) {
            $user_id = $user->id;
            $book_id = $request->get('book_id');
            
            //Check if book exists before user can rent it.
            $bookDetails = $this->book->getBookById($book_id);
            if ($bookDetails) {

            	// Check if user have already rented the book.
            	$isExists = $this->userBook->checkIfBookAlreadyRented($user_id, $book_id);
                if (!$isExists) {
	                
	                $this->userBook->rentBook([
	            	    'u_id'=> $user_id,
	            	    'b_id'=> $book_id
	                ]);

	                $response['success']['status'] = 200;
	                $response['success']['message'] = 'Book rented successfully.';
                } else {
                	$response['errors']['status'] = 400;
                    $response['errors']['message'] = 'You have already rented this book.';
                }
            } else {
                $response['errors']['status'] = 404;
                $response['errors']['message'] = 'Book not found.';
            }
        } else {
        	$response['errors']['status'] = 500;
            $response['errors']['message'] = 'Whoops, something went wrong.';
        }

        return response()->json($response);
    }

    public function returnBook(Request $request, $book_id) {
        $response = [];
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        if ($user) {
        	$user_id = $user->id;
        	//Check if book exists before user can rent it.
            $bookDetails = $this->book->getBookById($book_id);
            if ($bookDetails) {

            	// Check if user have rented the book or not. Then only he/she can return it.
            	$isExists = $this->userBook->checkIfBookAlreadyRented($user_id, $book_id);
                if ($isExists) {
	                
	                $this->userBook->returnBook($user_id, $book_id);

	                $response['success']['status'] = 200;
	                $response['success']['message'] = 'Book returned successfully.';
                } else {
                	$response['errors']['status'] = 400;
                    $response['errors']['message'] = 'You need to rent the book then only you can return it.';
                }
            } else {
                $response['errors']['status'] = 404;
                $response['errors']['message'] = 'Book not found.';
            }
        } else {
            $response['errors']['status'] = 500;
            $response['errors']['message'] = 'Whoops, something went wrong.';
        }

        return response()->json($response);
    }

    public function rentedBooks(Request $request) {
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        if ($user) {
            $user_id = $user->id;
            $rentedBooks = [];
            $results = $this->book->getRentedBooks($user_id);
            if ($results) {
                foreach ($results as $key => $value) {
                    $rentedBooks[] = array(
                        'book_name' => $value->book_name,
                        'author' => $value->author,
                        'cover_image' => url('/cover_images'.'/'.$value->cover_image)
                    );
                }
                $response['success']['status'] = 200;
                $response['success']['data'] = $rentedBooks;    
            } else {
                $response['errors']['status'] = 500;
                $response['errors']['message'] = 'No data available.';
            }
            
        } else {
            $response['errors']['status'] = 500;
            $response['errors']['message'] = 'Whoops, something went wrong.';
        }

        return response()->json($response);
    }
}
