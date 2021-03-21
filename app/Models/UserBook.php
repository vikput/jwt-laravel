<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBook extends Model
{
    protected $table = 'user_book';
    public $timestamps = false;

    public function rentBook($data) {
    	UserBook::insert($data);
    }

    public function checkIfBookAlreadyRented($user_id, $book_id) : bool {
    	$result = UserBook::where('u_id', $user_id)->where('b_id', $book_id)->get();
    	return count($result) >= 1 ? true : false;
    }

    public function returnBook($user_id, $book_id) {
        UserBook::where('u_id', $user_id)->where('b_id', $book_id)->delete();
    }
}
