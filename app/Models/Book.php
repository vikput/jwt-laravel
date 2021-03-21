<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Book extends Model
{
    protected $table = 'books';
    public $timestamps = false;

    public function create($data) {
        return Book::insertGetId($data);
    }

    public function updateCoverName($book_id, $cover_name) {
    	$book = Book::find($book_id);
    	$book->cover_image = $cover_name;
    	$book->save();
    }

    public function fetchAll() {
    	return Book::all();
    }

    public function getBookById($book_id) {
    	return Book::find($book_id);
    }

    public function getRentedBooks($user_id) {
        return Book::select('book_name', 'author', 'cover_image')
               ->join('user_book', function($join) use ($user_id){
                    $join->on('user_book.b_id', '=', 'books.id')
                        ->on('user_book.u_id', '=', DB::raw($user_id));
               })
               ->get();

    }
}
