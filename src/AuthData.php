<?php

declare( strict_types = 1 );

namespace Ocolin\BxAuth;

class AuthData
{
    /**
     * @var int Database ID for auth user.
     */
    public int $id;

    /**
     * @var int Auth user access level.
     */
    public int $access;

    /**
     * @var string Auth user description.
     */
    public string $descr;

    /**
     * @var string Auth user email address.
     */
    public string $email;

    /**
     * @var string Auth user username.
     */
    public string $user;

    /**
     * @var string Auth user first name.
     */
    public string $fname;

    /**
     * @var string Auth user last name.
     */
    public string $lname;

    /**
     * @var string Auth user password hash.
     */
    public string $password;
}