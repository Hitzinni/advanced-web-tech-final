<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Helpers\Database;

/**
 * Base Model Class
 * Provides a basic structure for other models, primarily handling database connection initialization.
 */
abstract class BaseModel
{
    /** 
     * @var PDO The database connection instance. 
     * Accessible by child classes.
     */
    protected $db;
    
    /**
     * Constructor
     * Automatically obtains a database connection instance using the Database helper class
     * and assigns it to the protected $db property.
     */
    public function __construct()
    {
        // Get the singleton PDO instance from the Database helper
        $this->db = Database::getInstance();
    }
} 