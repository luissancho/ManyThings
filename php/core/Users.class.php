<?php

namespace ManyThings\Core;

class Users extends ModelAdmin
{
    public $level = 0;
    public $roleId = 0;

    protected $meta = [
        'caption' => 'Users',
        'sortname' => 'id',
        'sortorder' => 'desc',
        'edit' => false,
        'add' => false,
        'fields' => [
            'id' => [
                'name' => 'User ID'
            ],
            'username' => [
                'name' => 'Name',
                'width' => '200',
                'wheretype' => 'like'
            ],
            'email' => [
                'name' => 'Email',
                'width' => '200',
                'wheretype' => 'like'
            ]
        ]
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);

        if (!empty($this->id)) {
            $userAdmin = AdminUsers::getRowBy('user_id', $this->id);

            if (!empty($userAdmin)) {
                $this->level = $userAdmin['level'];
                $this->roleId = $userAdmin['role_id'];
            } else {
                $this->level = 1;
            }
        }
    }

    public function create($values)
    {
        $config = $this->di->config;

        $errorList = self::validateCreate($values);
        if ($errorList->hasItems()) {
            throw new ArgumentException($errorList);
        }

        if (empty($values['class'])) {
            $values['status'] = 'member';
        }

        if (empty($values['lang'])) {
            $langs = $config->langs->toArray();
            $values['lang'] = array_shift($langs);
        }

        if (empty($values['timezone'])) {
            $values['timezone'] = $config->date->timezone;
        }

        $values['password'] = self::getPasswordEnc($values['password'], $config->app->encpass);

        parent::create($values);

        $usernameLink = Utils::makeLink($values['username']);
        $link = $usernameLink . '_' . $this->id;

        $this->update([
            'username_link' => $usernameLink,
            'link' => $link
        ]);

        return $this->id;
    }

    public function update($values)
    {
        $config = $this->di->config;
        $request = $this->di->request;

        $data = $this->data;
        $values = $this->getUpdateData($values);

        $errorList = self::validateUpdate($values);

        if (self::emailExists($values['email'], $this->id)) {
            $errorList->newItem('email', _T('Email is already registered.'));
        }

        if ($errorList->hasItems()) {
            throw new ArgumentException($errorList);
        }

        if ($values['lang'] != $data['lang']) {
            $request->setcookie('lang', $values['lang']);
        }

        // Upload photo
        if (!empty($values['photo'])) {
            $name = $this->id;
            $ext = strtolower(array_pop(explode('.', $values['photo']['name'])));
            Utils::uploadImageFixed($values['photo']['tmp_name'], ABSPATH . 'resources/photos/users/' . $name . '.' . $ext, 75, 75);
            $values['photo'] = $name . '.' . $ext;
        }

        if ($values['password'] != $data['password']) {
            $values['password'] = self::getPasswordEnc($values['password'], $config->app->encpass);
        }

        $changed = parent::update($values);

        return $changed;
    }

    public function updatePassword($password)
    {
        $config = $this->di->config;

        $errorList = self::validatePassword($password);
        if ($errorList->hasItems()) {
            throw new ArgumentException($errorList);
        }

        $changed = $this->update([
            'password' => self::getPasswordEnc($password, $config->app->encpass)
        ]);

        return $changed;
    }

    public function delete()
    {
        $this->update([
            'email' => str_replace('@', '-suf@', $this->data['email']),
            'active' => false
        ]);

        return true;
    }

    public function activate()
    {
        $this->update([
            'email' => str_replace('-suf@', '@', $this->data['email']),
            'active' => true
        ]);

        return true;
    }

    public static function emailExists($email, $id = 0)
    {
        $user = self::getRowBy('email', $email, true);

        if (!empty($user) && ($id == 0 || $user->id != $id)) {
            return true;
        }

        return false;
    }

    public static function usernameExists($username, $id = 0)
    {
        $user = self::getRowBy('username', $username, true);

        if (!empty($user) && ($id == 0 || $user->id != $id)) {
            return true;
        }

        return false;
    }

    public static function createPassword()
    {
        return Utils::getStringCode(8);
    }

    public static function getPasswordEnc($password, $enc)
    {
        switch ($enc) {
            case 'md5':
                return md5($password);
            case 'hash':
                return Utils::getPasswordHash($password);
            default:
                return $password;
        }
    }

    public static function validateCreate($values)
    {
        $errorList = new ErrorList();

        if (empty($values['email'])) {
            $errorList->newItem('email', _T('Email is empty.'));
        }

        if (empty($values['username'])) {
            $errorList->newItem('username', _T('Username is empty.'));
        }

        if (empty($values['password'])) {
            $errorList->newItem('password', _T('Password is empty.'));
        }

        return $errorList;
    }

    public static function validateUpdate($values)
    {
        $errorList = new ErrorList();

        if (array_key_exists('email', $values) && empty($values['email'])) {
            $errorList->newItem('email', _T('Email is empty.'));
        }

        if (array_key_exists('username', $values) && empty($values['username'])) {
            $errorList->newItem('username', _T('Username is empty.'));
        }

        if (array_key_exists('password', $values) && empty($values['password'])) {
            $errorList->newItem('password', _T('Password is empty.'));
        }

        return $errorList;
    }

    public static function validatePassword($password)
    {
        $errorList = new ErrorList();

        if (empty($password)) {
            $errorList->newItem('password', _T('Password is empty.'));
        } elseif (strlen($password) < 5) {
            $errorList->newItem('password', _T('Password is too short.'));
        } elseif (strlen($password) > 20) {
            $errorList->newItem('password', _T('Password is too long.'));
        }

        return $errorList;
    }
}
