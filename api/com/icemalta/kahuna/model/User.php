<?php
namespace com\icemalta\kahuna\model;

require_once 'com/icemalta/kahuna/model/DBConnect.php';

use \PDO;
use \JsonSerializable;
use com\icemalta\kahuna\model\DBConnect;

class User implements JsonSerializable
{
    private static $db;
    private int|string $id = 0;
    private string $username;
    private string $email;
    private string $password;
    private string $accessLevel = 'user';

    public function __construct(string $username, ?string $email, string $password, ?string $accessLevel = 'user', int|string $id = 0)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->accessLevel = $accessLevel;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function getID(): int
    {
        return $this->id;
    }
    public function setID(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getAccessLevel(): string
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(string $accessLevel): self
    {
        $this->accessLevel = $accessLevel;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public static function authenticate(User $user): ?User
    {
        if (!self::$db) {
            self::$db = DBConnect::getInstance()->getConnection();
        }
        
        try {
            $sql = 'SELECT * FROM User WHERE userName = :username AND password = :password';
            $sth = self::$db->prepare($sql);
            $sth->bindValue('username', $user->username);
            $sth->bindValue('password', $user->password);
            $sth->execute();

            $result = $sth->fetch(PDO::FETCH_OBJ);
            
            if ($result && $result->userName !== null) {
                return new User(
                    $result->userName,
                    $result->email,
                    $result->password,
                    $result->accessLevel,
                    $result->id
                );
            }
            return null;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function signUp(User $user): User
    {
        if (!self::$db) {
            self::$db = DBConnect::getInstance()->getConnection();
        }
        
        try {
            $sql = 'INSERT INTO User(userName, email, password, accessLevel) VALUES (:userName, :email, :password, :accessLevel)';
            $sth = self::$db->prepare($sql);
            
            $sth->bindValue('userName', $user->getUsername());
            $sth->bindValue('email', $user->getEmail());
            $sth->bindValue('password', $user->getPassword());
            $sth->bindValue('accessLevel', $user->getAccessLevel());
            
            $sth->execute();

            if ($sth->rowCount() > 0) {
                $user->setId(self::$db->lastInsertId());
            } else {
                throw new \PDOException("Failed to create user");
            }

            return $user;
        } catch (\PDOException $e) {
            error_log("Database error during signup: " . $e->getMessage());
            throw $e;
        }
    }

    function checkToken(array $requestData): bool
    {
        if (!isset($requestData['token']) || !isset($requestData['user'])) {
            return false;
        }
        $token = new AccessToken($requestData['user'], $requestData['token']);
        return AccessToken::verify($token);
    }
}