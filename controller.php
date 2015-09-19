<?php
require("model.php");

class Controller
{
    //--------Variables------------
    private $model;
    private $functions;
    //--------Functions------------
    //Constructor
    function __construct()
    {
        //initialize private variables
        $this->model  = new Model();
        $this->functions = array(
            "login" => "loginPage",
            "home" => "homePage",

            "attemptRegistration" => "attemptRegistration",
            "attemptLogin" => "attemptLogin",
            "logout" => "logout",
            "postJob" => "postJob"



        );
        date_default_timezone_set("UTC");
        if (strlen($_GET['function']) > 0) {
            $functionParams = explode("/", (isset($_GET['function']) ? $_GET['function'] : null));
        } else {
            $functionParams = false;
        }
        //page is always query or home
        $page     = $_GET['page'];
        //Handle Page Load
        $page === "home" ? $this->homePage() : $this->$page($functionParams);
    }
    //Creates a custom header to redirect the page
    private function redirect($url)
    {
        header("Location: /" . $url);
    }
    //Loads view from the views folder and extracts data into the view
    private function loadView($view, $data = null)
    {
        if (is_array($data)) {
            extract($data);
        }
        require("views/" . $view . ".php");
    }
    //Loads page from appropriate views
    private function loadPage($view, $data = null, $error = null)
    {
        if ($error !== null) {
            $this->loadView("components/error", $error);
        }
        $this->loadView("components/header");
        $this->loadView("pages/".$view, $data);
        $this->loadView("components/footer");
    }
    //Security Function
    private function checkAuth()
    {
        if (isset($_COOKIE['Auth'])) {
            $auth_hash = $_COOKIE['Auth'];
            setcookie("Auth", $auth_hash, time()+60*60*24*30, "/");
            //setcookie("Auth", $hash, time()+3600*24*30, "/", "http://whistlet.com");
            return $this->model->userForAuthHash($auth_hash);
        } else {
            return false;
        }
    }
    //Page Handling
    private function query($function)
    {
        $endpoint = $function[0];
        if (array_key_exists($endpoint, $this->functions)) {
            $toPage =  $this->functions[$endpoint];
            $this->$toPage();
        } else {
            $this->profilePage($endpoint);
        }
    }
    //Other Helpful Functions
    private function hasInvalidChars($str)
    {
        return preg_match('/[^A-Za-z0-9]/', $str);
    }
    //*******************************************************
    //PAGES
    //*******************************************************

    //Login Page
    private function loginPage() //params is an array of errors
    {
        $user = $this->checkAuth();
        if ($user !== false) { //Whiz is logged in
            $this->redirect("");
        } else { //Whiz is not logged in
            $error = null;
            if ( isset($_GET['error']) ){
               $error = $_GET['error'];
            }
            $this->loadPage("login", array(), $error);
        }
    }
    //Home page
    private function homePage()//params are typically empty
    {
        $cur_user = $this->checkAuth();
        if ($cur_user === false) { //Load login with error
            $this->loadPage("login", null);
        } else { //load home page
            $cur_user    = $this->model->getWhizData($cur_user->whiz_id);
            $jobs = $this->model->fetchJobs($cur_user->whiz_id);
            $this->loadPage("home", array(
                "cur_user" => $cur_user, "jobs" => $jobs
            ), null);
        }
    }



    private function settingsPage()
    {
        $cur_user = $this->checkAuth();
        if ($cur_user === false) { //Load login with error
            $this->loadPage("login", null, array(-1));
        } else {
            $cur_user    = $this->model->getWhizData($cur_user->user_id, $cur_user->user_id);
            $this->loadPage("settings", array(
                "cur_user" => $cur_user,
            ), null);
        }
    }






    private function loadHome()
    {
        $cur_user = $this->checkAuth();
        if ($cur_user === false) { //Load login with error
            echo -1;
        } else { //fetch home feed broadcasts
            //Add code here
        }
    }
    private function getCoordinates($address){

        $address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern

        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";

        $response = file_get_contents($url);

        $json = json_decode($response,TRUE); //generate array object from the response from the web

        return array($json['results'][0]['geometry']['location']['lat'], $json['results'][0]['geometry']['location']['lng']);

    }
    private function postJob(){
        //todo with ${POST}
        $coordinates = $this->getCoordinates($_POST["address"]);
        $this->model->createJob($_POST["job_name"], $_POST["job_description"], $_POST["job_price"], $coordinates[0], $coordinates[1]);
        // echo ($_POST["job_name"].",".$_POST["job_description"].",".$_POST["job_price"].",".$coordinates[0].",".$coordinates[1]);
        $this->loadPage("jobSuccess", array(), null);

    }

    private function checkWhizname()
    {
        $uname = $_GET["username"];
        //bool user exists
        $response = $userExists = $this->model->usernameExists($uname);
        $userInvalidChars = $this->hasInvalidChars($uname);

        if(!$userExists && !$userInvalidChars ){
            echo 1;
        } else if(($userInvalidChars)){
            echo -8;
        } else{
            echo -4;
        }
    }



    private function attemptRegistration()
    {
        if(!true){
        // if (array_key_exists($_POST['username'], $this->functions)) {
        //     $this->redirect("login/9");
        // } else if ($_POST['email'] == "" || strpos($_POST['email'], "@") === false || strpos($_POST['email'], "+") === true) {


        //     $this->redirect("login/5");
        // } else if ($_POST['username'] == "") {

        //     $this->redirect("login/6");
        // } else if ($this->hasInvalidChars($_POST['username'])) {

        //     $this->redirect("login/8");
        // }else if (strlen($_POST['password']) < 6) {

        //     $this->redirect("login/4");
        // } else if ($_POST['password'] != $_POST['password2']) {

        //     $this->redirect("login/3");
        } else {
            $curSalt = chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 );
            $pass       = hash('sha256', $curSalt.$_POST['password']);
            $signupInfo = array(
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $pass,
                'full_name' => $_POST['full_name'],
                'salt' => $curSalt
            );
            $resp       = $this->model->registerWhiz($signupInfo, true);
            if ($resp !== false) {
                echo "success";
                //$this->redirect("");
            } else {
                echo "failure";
                // $this->redirect("login/" . $resp);
            }
        }
    }

    //add later: on redirect, append page user was currently on when logged in to empty string******************************
    private function attemptLogin()
    {
        $salt = $this->model->getSaltForUsername($_POST['username']);
        $whiz_id = $this->model->getWhizIdForUsername($_POST['username']);
        $pass      = hash('sha256', $salt.$_POST['password']);
        $loginInfo = array(
            'whiz_id' => $whiz_id,
            'password' => $pass
        );
        if ($this->model->attemptLogin($loginInfo, true)) {
            $this->redirect("");
        } else {
            $this->redirect("login?error=-123");
        }
    }
    private function logout()
    {
        echo "Logging out";
        $this->model->logoutWhiz($_COOKIE['Auth'], true);
        $this->redirect("");
    }





}

$controller = new Controller();