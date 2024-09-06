<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

// var_dump(User::fields());



// var_dump(User::where('name','maj','like'));

// // // get all users
// var_dump(User::all());



// // create a new user
$user = new User();
$user->name = "amir";
// $user->is_admin = true;
$user->save();

var_dump($user);

// // // // get one user
// $user = User::find($user->id);
// var_dump($user);
// var_dump(json_encode($user));
// // update the user
// $user->name = "majid";
// $user->is_admin = false;
// $user->save();

// var_dump(json_encode($user));

// // // delete the user
// $user->delete();
// unset($user);
