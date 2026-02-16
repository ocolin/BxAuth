<?php

declare( strict_types = 1 );

namespace Ocolin\BxAuth;

use Ocolin\EasyDB\DB;
use Ocolin\GlobalType\GT;
use Exception;
use PDO;

class BxAuth
{
    public PDO $db;


/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * Setup class to make database call to Billmax.
     *
     * @param string|null $host Hostname of database.
     * @param string|null $name Name of database.
     * @param string|null $user Username of database.
     * @param string|null $pass Password of database.
     * @throws Exception
     */
    public function __construct(
        ?string $host = null,
        ?string $name = null,
        ?string $user = null,
        ?string $pass = null,
    )
    {
        $this->db = DB::getHandler(
            host: $host ?? GT::envStringNull( name: 'BILLMAX_DB_HOST' ) ?? 'localhost',
            name: $name ?? GT::envStringNull( name: 'BILLMAX_DB_NAME' ) ?? 'billmax',
            user: $user ?? GT::envString( name: 'BILLMAX_DB_USER' ),
            pass: $pass ?? GT::envString( name: 'BILLMAX_DB_PASS' ),
        );
    }



/*
----------------------------------------------------------------------------- */

    /**
     * Login using Billmax server authentication.
     *
     * @param string $user Username of Billmax Auth user.
     * @param string $pass Password of Billmax Auth user.
     * @param bool $session Start a PHP session.
     * @return false|object Auth data or false if not found.
     */
    public function login(
        string $user, string $pass, bool $session = false
    ) : false | object
    {
        $data = $this->get_User_From_DB( user: $user );
        if( $data === false ) { return false; }

        $salt = self::get_Salt( pass: $data->password );
        $hash = self::hash_Password( pass: $pass, salt: $salt );

        if( $hash !== $data->password ) { return false; }

        if( $session === true ) {
            if( session_status() === PHP_SESSION_NONE ) {
                session_start();
            }
            $_SESSION['LOGGED_IN'] = true;
            $_SESSION['AUTH'] = $data;
        }

        return $data;
    }



/* LOG OUT
----------------------------------------------------------------------------- */

    /**
     * To log out, we destroy the cookie for the session, then destroy the
     * session data.
     *
     * @return void
     */
    public function logout() : void
    {
        if( session_status() === PHP_SESSION_ACTIVE ) {
            if( ini_get( option: "session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                        name: (string)session_name(),
                       value: '',
                    expires_or_options: time() - 42000,
                        path: $params["path"],
                      domain: $params["domain"],
                      secure: $params["secure"],
                    httponly: $params["httponly"]
                );
            }
            session_unset();
            session_destroy();
        }

    }



/* CHECK LOGIN
----------------------------------------------------------------------------- */

    /**
     * Check if user is logged in.
     *
     * @return AuthData | false User data if logged in, false if not.
     */
    public static function check_Login() : AuthData | false
    {
        if(
            session_status() === PHP_SESSION_ACTIVE AND
            !empty( $_SESSION['LOGGED_IN'] )
        ) {
            $data = $_SESSION['AUTH'];
            unset( $data->password );
            return $data;
        }

        return false;
    }



/* GET USER DATA
----------------------------------------------------------------------------- */

    /**
     * Get user data from Billmax auth table.
     *
     * @param string $user Username of Billmax user to authenticate.
     * @return AuthData|false Data object or false if not found.
     */
    public function get_User_From_DB( string $user ) : AuthData | false
    {
        $query = $this->db->prepare( query: "
            SELECT 
                number AS id, access, descr, email, user, fname, lname, password
            FROM auth
            WHERE user = :user
            LIMIT 1
        ");
        $query->bindParam( param: ":user", var: $user );
        $query->setFetchMode( PDO::FETCH_CLASS, AuthData::class );
        $query->execute();

        $output = $query->fetch();
        if( !$output instanceof AuthData ) { return false; }

        return $output;
    }



/* HASH PASSWORD
----------------------------------------------------------------------------- */

    /**
     * Convert raw password into a hash using its salt.
     *
     * @param string $pass Raw password to hash.
     * @param string $salt Salt to use for hashing.
     * @return string Password hash.
     */
    public static function hash_Password( string $pass, string $salt ) : string
    {
        return crypt( string: $pass, salt: $salt );
    }



/* GET PASSWORD SALT
----------------------------------------------------------------------------- */

    /**
     * The beginning of the hash in the database starts with a salt that also
     * says what encoding method was used. This function may be updated for
     * different algorithms in the future. Right now MD5 used.
     *
     * @param string $pass Salt is stored in first 6 characters of hash.
     * @return string Salt from stored hash.
     */
    public static function get_Salt( string $pass ) : string
    {
        return mb_substr( string: $pass, start: 0, length: 6 );
    }

}