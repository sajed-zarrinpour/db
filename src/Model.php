<?php

namespace SajedZarinpour\DB;

/**
 * this file contains the base model which entities will extend. the important note here is the use of traits to controll database adaptors.
 */
abstract class Model
{
    use Mysql;

}