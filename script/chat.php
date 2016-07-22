<?php

namespace MY_Common;

define('DB_USERNAME',       'root');
define('DB_PASSWORD',       '');
define('DB_HOST',           'localhost');
define('DB_NAME',           'minichat');
define('CHAT_HISTORY',      '150');

abstract class Model
{
    public $db;

    public function __construct()
    {
        $this->db = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    }
}

abstract class Controller
{
    private $_request, $_response, $_query, $_post;
    protected $_currentAction, $_defaultModel;

    const ACTION_POSTFIX = 'Action';
    const ACTION_DEFAULT = 'indexAction';

    public function __construct()
    {
        $this->_request  = &$_REQUEST;
        $this->_query    = &$_GET;
        $this->_post     = &$_POST;
        $this->init();
    }

    public function init()
    {
        $this->dispatchActions();
        $this->render();
    }

    public function dispatchActions()
    {
        $action = $this->getQuery('action');
        if ($action && $action .= self::ACTION_POSTFIX) {
            if (method_exists($this, $action)) {
                $this->setResponse(
                    call_user_func(array($this, $action), array())
                );
            } else {
                $this->setHeader("HTTP/1.0 404 Not Found");
            }
        } else {
            $this->setResponse(
                call_user_func(array($this, self::ACTION_DEFAULT), array())
            );
        }
        return $this->_response;
    }

    public function render()
    {
        if ($this->_response) {
            if (is_scalar($this->_response)) {
                echo $this->_response;
            } else {
                throw new \Exception('Response content must be type scalar');
            }
            exit;
        }
    }

    public function indexAction()
    {
        return null;
    }

    public function setResponse($content)
    {
        $this->_response = $content;
    }

    public function setHeader($params)
    {
        if (! headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }
        return $this;
    }

    public function setModel($namespace)
    {
        $this->_defaultModel = $namespace;
        return $this;
    }


    public function getRequest($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_request[$param]) ?
                $this->_request[$param] : $default;
        }
        return $this->_request;
    }

    public function getQuery($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_query[$param]) ?
                $this->_query[$param] : $default;
        }
        return $this->_query;
    }

    public function getPost($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_post[$param]) ?
                $this->_post[$param] : $default;
        }
        return $this->_post;
    }

    public function getModel()
    {
        if ($this->_defaultModel && class_exists($this->_defaultModel)) {
            return new $this->_defaultModel;
        }
    }

    public function sanitize($string, $quotes = ENT_QUOTES, $charset = 'utf-8')
    {
        return htmlentities($string, $quotes, $charset);
    }
}

abstract class Helper
{

}


namespace MY_Chat;
use MY_Common;
class Model extends MY_Common\Model
{

    public function getMessages($limit = CHAT_HISTORY, $reverse = true)
    {
        $response = $this->db->query("(SELECT * FROM messages
            ORDER BY `date` DESC LIMIT {$limit}) ORDER BY `date` DESC");

        $result = array();
        while($row = $response->fetch_object()) {
            $result[] = $row;
        }
        $response->free();
        return $result;
    }

    public function addMessage($username, $message)
    {
        $username = addslashes($username);
        $message = addslashes($message);

        return (bool) $this->db->query("INSERT INTO messages
            VALUES (NULL, '{$username}', '{$message}', NOW())");
    }

    public function __destruct()
    {
        if ($this->db) {
            $this->db->close();
        }
    }

}

class Controller extends MY_Common\Controller
{
    protected $_model;

    public function __construct()
    {
        $this->setModel('MY_Chat\Model');
        parent::__construct();
    }

    public function listAction()
    {
        $this->setHeader(array('Content-Type' => 'application/json'));
        $messages = $this->getModel()->getMessages();
        return json_encode($messages);
    }

    public function saveAction()
    {
        $username = $this->getPost('username');
        $message = $this->getPost('message');

        $result = array('success' => false);
        if ($username && $message) {
            $result = array(
                'success' => $this->getModel()->addMessage($username, $message)
            );
        }

        $this->setHeader(array('Content-Type' => 'application/json'));
        return json_encode($result);
    }
}

$chatApp = new Controller(); 

?>