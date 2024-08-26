<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

// $one = new stdClass();
// $one->first = 'test';
// $one->second = 'ronak';

// $second = clone $one;

// $one->first='varies';

// var_dump($one);
// var_dump($second);
// die;




// var_dump(User::where('name','maj','like'));

// get all users
// var_dump(User::all());



// // create a new user
// $user = new User();
// $user->name = "amir";
// $user->save();

// var_dump($user);

// // get one user
$user = User::find(38);
var_dump($user);
// // update the user
// $user->name = "majid";
// $user->save();

// var_dump(json_encode($user));

// // delete the user
// // $user->delete();
// // unset($user);
