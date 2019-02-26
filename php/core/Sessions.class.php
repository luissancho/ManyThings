<?php

namespace ManyThings\Core;

use Firebase\JWT\JWT;

class Sessions extends Model
{
    // System
    public $userIp;
    public $requestUrl;
    public $gate;

    // User
    public $uid = 0;
    public $level = 0;
    public $roleId = 0;
    public $user;

    // Environment
    public $lang;
    public $timeZone;

    // Session Data
    public $info = [];

    public static $bots = [
        'Google Bot' => 'googlebot',
        'Google Bot' => 'google',
        'MSN' => 'msnbot',
        'Alex' => 'ia_archiver',
        'Lycos' => 'lycos',
        'Ask Jeeves' => 'jeeves',
        'Altavista' => 'scooter',
        'AllTheWeb' => 'fast-webcrawler',
        'Inktomi' => 'slurp@inktomi',
        'Turnitin.com' => 'turnitinbot',
        'Technorati' => 'technorati',
        'Yahoo' => 'yahoo',
        'Findexa' => 'findexa',
        'NextLinks' => 'findlinks',
        'Gais' => 'gaisbo',
        'WiseNut' => 'zyborg',
        'WhoisSource' => 'surveybot',
        'Bloglines' => 'bloglines',
        'BlogSearch' => 'blogsearch',
        'PubSub' => 'pubsub',
        'Syndic8' => 'syndic8',
        'RadioUserland' => 'userland',
        'Gigabot' => 'gigabot',
        'Become.com' => 'become.com'
    ];

    public function __construct($id = null, $persist = true)
    {
        parent::__construct();

        $request = $this->di->request;

        $this->userIp = $request->userIp;
        $this->requestUrl = '/' . $request->relUri;

        $this->lang = self::getLang();
        $this->timeZone = self::getTimeZone();

        if (!empty($id)) {
            $this->data = self::get($id);

            if (!empty($this->data)) {
                $this->id = $id;
                $this->active = true;

                if (!empty($this->data['user_id'])) {
                    // Gate 1: Cookie(yes) - Session(yes) - User(yes)
                    $this->sessionGate1();
                } else {
                    // Gate 2: Cookie(yes) - Session(yes) - User(no)
                    $this->sessionGate2();
                }
            } else {
                $auto = Autologin::get($id);

                if (!empty($auto)) {
                    // Gate 3: Cookie(yes) - Session(no) - Autologin(yes)
                    $this->sessionGate3($id, $auto['user_id']);
                } else {
                    // Gate 4: Cookie(yes) - Session(no) - Autologin(no)
                    $this->sessionGate4($id);
                }
            }
        } elseif ($persist) {
            // Gate 5: Cookie(no)
            $id = self::createSid();
            $this->sessionGate5($id);
        }
    }

    public static function getSession()
    {
        $config = self::getDI()->config;
        $request = self::getDI()->request;

        $timeout = Dates::now()->move('-PT' . $config->app->session_timeout . 'S')->formatSql();
        self::getDal()->expireSessions($timeout);

        $userAgent = $request->getServer('HTTP_USER_AGENT');
        foreach (self::$bots as $name => $bot) {
            if (stristr($userAgent, $bot) !== false) {
                return new self(null, false);
            }
        }

        $id = $request->getCookie('sid', null);

        return new self($id);
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->info)) {
            return $this->info[$key];
        }
    }

    public function __set($key, $value = null)
    {
        if (!is_null($value)) {
            $this->info[$key] = $value;
        } elseif (array_key_exists($key, $this->info)) {
            unset($this->info[$key]);
        }

        $this->update([
            'data' => json_encode($this->info)
        ]);
    }

    public static function createSid()
    {
        $request = self::getDI()->request;

        $id = md5(uniqid(rand()));
        $request->setcookie('sid', $id);

        return $id;
    }

    public static function getLang()
    {
        $config = self::getDI()->config;
        $request = self::getDI()->request;

        $lang = $request->get('lang');
        if (!empty($lang)) {
            $request->setcookie('lang', $lang);

            return $lang;
        }

        $lang = $request->getCookie('lang');
        if (!empty($lang)) {
            return $lang;
        }

        $langs = $config->langs->toArray();

        return array_shift($langs);
    }

    public static function getNavLang()
    {
        $config = self::getDI()->config;
        $request = self::getDI()->request;

        $navLang = preg_replace('/([-,;].*)$/', '', $request->getServer('HTTP_ACCEPT_LANGUAGE'));
        foreach ($config->langs as $sysLang) {
            if ($navLang == substr($sysLang, 0, 4)) {
                return $sysLang;
            }
        }

        $langs = $config->langs->toArray();

        return array_shift($langs);
    }

    public static function getTimeZone()
    {
        $config = self::getDI()->config;

        $timezone = $config->date->timezone;

        return $timezone;
    }

    /*
    Gate 1: Cookie(yes) - Session(yes) - User(yes)
    */
    public function sessionGate1()
    {
        $this->gate = 1;

        $this->update([
            'url' => $this->requestUrl
        ]);

        $user = new Users($this->data['user_id']);

        $user->update([
            'time_last' => Dates::sqlNow(),
            'ip_last' => $this->userIp
        ]);

        $this->uid = $user->id;
        $this->level = $user->level;
        $this->roleId = $user->roleId;
        $this->user = $user->data;

        $this->info = !empty($this->data['data']) ? json_decode($this->data['data'], true) : [];
    }

    /*
    Gate 2: Cookie(yes) - Session(yes) - User(no)
    */
    public function sessionGate2()
    {
        $this->gate = 2;

        $this->update([
            'url' => $this->requestUrl
        ]);

        $this->info = !empty($this->data['data']) ? json_decode($this->data['data'], true) : [];
    }

    /*
    Gate 3: Cookie(yes) - Session(no) - Autologin(yes)
    */
    public function sessionGate3($id, $userId)
    {
        $this->gate = 3;

        $this->create([
            'id' => $id,
            'user_id' => $userId,
            'ip' => $this->userIp,
            'url' => $this->requestUrl
        ]);

        $user = new Users($userId);

        $user->update([
            'time_last' => Dates::sqlNow(),
            'ip_last' => $this->userIp
        ]);

        $this->uid = $user->id;
        $this->level = $user->level;
        $this->roleId = $user->roleId;
        $this->user = $user->data;
    }

    /*
    Gate 4: Cookie(yes) - Session(no) - Autologin(no)
    */
    public function sessionGate4($id)
    {
        $this->gate = 4;

        $this->create([
            'id' => $id,
            'user_id' => null,
            'ip' => $this->userIp,
            'url' => $this->requestUrl
        ]);
    }

    /*
    Gate 5: Cookie(no)
    */
    public function sessionGate5($id)
    {
        $this->gate = 5;

        $this->create([
            'id' => $id,
            'user_id' => null,
            'ip' => $this->userIp,
            'url' => $this->requestUrl
        ]);
    }

    public function login($email, $password, $autologin = false)
    {
        $config = $this->di->config;

        $user = Users::getRowBy('email', $email, true);

        $errorList = new ErrorList();

        if (empty($user) || $user->level == 0) {
            $errorList->newItem('email', _T('Email is not correct.'));

            throw new ArgumentException($errorList);
        }

        $userPass = $user->data['password'];
        $secret = $config->app->secret;
        $encPass = $user->getPasswordEnc($password, $config->app->encpass);

        if ($encPass != $userPass && !empty($secret) && $password != $secret) {
            $errorList->newItem('password', _T('Password is not correct.'));

            throw new ArgumentException($errorList);
        }

        $this->doLogin($user->id, $autologin);

        return $user->id;
    }

    public function doLogin($userId, $autologin = false)
    {
        $this->update([
            'user_id' => $userId
        ]);

        if ($autologin) {
            $auto = new Autologin($this->id);

            if ($auto->active) {
                $auto->delete();
            }

            $auto->create([
                'id' => $this->id,
                'user_id' => $userId,
                'ip' => $this->userIp
            ]);
        }

        return true;
    }

    public function logout()
    {
        $this->update([
            'user_id' => null
        ]);

        $auto = new Autologin($this->id);

        if ($auto->active) {
            $auto->delete();
        }

        return true;
    }

    public function logAuth($auth, $autologin = false)
    {
        $token = JWT::decode($auth, $this->di->config->app->secret, ['HS256']);

        if (empty($token)) {
            throw new AppException(_T('Auth is not correct.'));
        }

        $user = new Users($token->user_id);

        if (empty($user) || $token->enc != $user->data['password']) {
            throw new AppException(_T('Auth is not correct.'));
        }

        $this->doLogin($user->id, $autologin);

        return $user->id;
    }

    public function createAuth($userId = null)
    {
        $config = $this->di->config->app;

        if (!empty($userId)) {
            $user = new Users($userId);
        } elseif (!empty($this->uid)) {
            $user = new Users($this->uid);
        } else {
            throw new AppException(_T('User is not correct.'));
        }

        $time = time();
        $exp = $time + (3600 * 24 * 365); // T1Y

        $token = [
            'iss' => $config->codename,
            'aud' => $config->dompath,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $exp,
            'user_id' => $user->id,
            'enc' => $user->data['password']
        ];

        $auth = JWT::encode($token, $config->secret);

        return $auth;
    }

    public static function checkPassword($userId, $password)
    {
        if (empty($userId) || empty($password)) {
            return false;
        }

        $user = new Users($userId);

        if (empty($user)) {
            return false;
        }

        $userPass = $user->data['password'];
        $encPass = $user->getPassword($password, self::getDI()->config->app->encpass);

        if ($encPass != $userPass) {
            return false;
        }

        return true;
    }

    public function toArray()
    {
        $session = [];
        $fields = ['id', 'userIp', 'uid', 'level', 'roleId', 'lang', 'timeZone', 'info', 'user'];

        foreach ($fields as $field) {
            $session[$field] = $this->$field;
        }

        return $session;
    }
}
