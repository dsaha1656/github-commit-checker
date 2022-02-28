<?php

require_once "Mail.php";
require_once "env.php";

function sendMail($to, $subject, $body) {
    require "env.php";

    $successMessage = 'Message successfully sent!';

    $replyTo = '';
    $name = '';

    $headers = array(
        'From' => $name . " <" . $from . ">",
        'To' => $to,
        'Subject' => $subject
    );
    $smtp = Mail::factory('smtp', array(
                'host' => $smtpHost,
                'port' => $smtpPort,
                'auth' => true,
                'username' => $username,
                'password' => $password
            ));

    $mail = $smtp->send($to, $headers, $body);

    if (PEAR::isError($mail)) {
        echo($mail->getMessage());
        return false;
    } else {
        return true;
    }

}

function getLastCommit($repo_user, $repo_name, $repo_branch='main') {
    $repo_url = "https://api.github.com/repos/$repo_user/$repo_name/branches/$repo_branch";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $repo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Whatsapp Web');
    $result = curl_exec($ch);
    curl_close($ch);
    try{
        $result = json_decode($result);
    }catch(Exception $e){
        return false;
    }
    return $result->commit->sha;
}

// get last commit
$repo_url = "https://github.com/$repo_owner/$repo_name";
$last_commit = getLastCommit($repo_owner, $repo_name, $repo_branch);
if(!$last_commit){
    echo "Error getting last commit";
    // send mail to support
    foreach($support_mail as $mail){
        sendMail($mail, $email_prefix." : Error getting Last commit", "Error getting last commit, please check the repo".$repo_url);
    }
    exit;
}

// get last commit from last commit file
$last_commit_file = __DIR__."/".$last_commit_file;
if(!file_exists($last_commit_file)){
    file_put_contents($last_commit_file, $last_commit);
    exit;
}
$last_commit_data = file_get_contents($last_commit_file);

// compare last commit and current commit
if($last_commit_data == $last_commit){
    echo "No changes";
    exit;
}else{
    // save the last commit to file
    file_put_contents($last_commit_file, $last_commit);
    // send mail to support
    foreach($support_mail as $mail){
        sendMail($mail, $email_prefix." : New commit", "New commit detected, please check the repo.".$repo_url);
    }
}



