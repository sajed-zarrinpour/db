<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;

// get all users
var_dump(User::all());



// create a new user
$user = new User();
$user->name = "amir";
$user->save();

var_dump($user);

// get one user
$user = User::find(38);
var_dump($user);
// update the user
$user->name = "majid";
$user->save();

var_dump(json_encode($user));

// delete the user
// $user->delete();
// unset($user);
