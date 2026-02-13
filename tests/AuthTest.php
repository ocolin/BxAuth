<?php

declare( strict_types = 1 );

namespace Ocolin\BxAuth\tests;

use Ocolin\BxAuth\BxAuth;
use PHPUnit\Framework\TestCase;
use PDO;

class BxAuthTest extends TestCase
{
    public static BxAuth $bxAuth;

    public function testGetUser() : void
    {
        $output = self::$bxAuth->get_User_From_DB( user: $_ENV['TEST_USERNAME']);
        self::assertIsObject( $output );
        self::assertEquals( $_ENV['TEST_USERNAME'], $output->user );
        //print_r( $output );
    }

    public function testGetSalt() : void
    {
        $output = self::$bxAuth::get_Salt( pass: $_ENV['TEST_HASH'] );
        self::assertIsString( $output );
        self::assertEquals( $output, $_ENV['TEST_SALT'] );
        //echo "$output\n";
    }

    public function testHashPassword() : void
    {
        $data = self::$bxAuth->get_User_From_DB( user: $_ENV['TEST_USERNAME']);
        $salt = self::$bxAuth::get_Salt( pass: $data->password );
        $output = self::$bxAuth->hash_Password(
            pass: $_ENV['TEST_PASSWORD'],
            salt: $salt
        );
        self::assertEquals( $data->password, $output );
        //echo "\n$output - {$data->password}\n";
    }

    public function testLoginPass() : void
    {
        $output = self::$bxAuth->login(
            user: $_ENV['TEST_USERNAME'],
            pass: $_ENV['TEST_PASSWORD'],
            session: true
        );
        //print_r( $output );
        self::assertNotFalse( $output );
        self::assertEquals( $_ENV['TEST_USERNAME'], $output->user );
        self::assertNotEmpty( $_SESSION );
        //print_r( $_SESSION );
    }

    public function testCheckLoginGood() : void
    {
        $output = self::$bxAuth->check_login();
        self::assertTrue( $output );
        //var_dump( $output );
    }

    public function testLoginFailUser() : void
    {
        $output = self::$bxAuth->login(
            user: 'UnknownUser',
            pass: 'UnknownPassword'
        );
        self::assertFalse( $output );
    }

    public function testLoginFailPass() : void
    {
        $output = self::$bxAuth->login(
            user: $_ENV['TEST_USERNAME'],
            pass: $_ENV['TEST_PASSWORD'] . 'kjshdfs'
        );
        self::assertFalse( $output );
    }

    public function testLogout() : void
    {
        self::$bxAuth->logout();
        self::assertEmpty( $_SESSION );
        //print_r( $_SESSION );
    }


    public function testCheckLoginBad() : void
    {
        $output = self::$bxAuth->check_login();
        self::assertFalse( $output );
    }


    public static function setUpBeforeClass(): void
    {
        self::$bxAuth = new BxAuth(
            host: $_ENV['TEST_HOST'],
            name: $_ENV['TEST_NAME'],
            user: $_ENV['TEST_USER'],
            pass: $_ENV['TEST_PASS'],
        );
    }

}
