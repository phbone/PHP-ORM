<?php

/**
 * GIT DEPLOYMENT SCRIPT
 *
 * Used for automatically deploying websites via github or bitbucket, more deets here:
 *
 * 		https://gist.github.com/1809044
 */
// The commands
mail('bryanwan23@gmail.com', 'hueclues server notice', "The last push has been updated");
$commands = array(
    'sudo mkdir /var/www/html/fries',
    'cd /var/www/html',
    'sudo git pull origin master'
);
// Run the commands for output
$output = '';
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
foreach ($commands as $command) {
    // Run it
    $tmp = shell_exec($command);
    // Output
    $output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
    $output .= htmlentities(trim($tmp)) . "\n";
}

// Make it pretty for manual user access (and why not?)
?>
<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <title>GIT DEPLOYMENT SCRIPT</title>
    </head>
    <body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
        <pre>
 .  ____  .    ____________________________
 |/      \|   |                            |
[| <span style="color: #FF0000;">&hearts;    &hearts;</span> |]  | Git Deployment Script v0.1 |
 |___==___|  /              &copy; oodavid 2012 |
              |____________________________|
 
            <?php echo $output; ?>
        </pre>
    </body>
</html>