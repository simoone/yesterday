<html>
    <head>
        <title>Yesterday | Daily Digest for Asana</title>

        <style type="text/css">
            *{font-family: "Courier New";font-size: 12px;color: #f8f8f0;background: #272822;text-decoration: none;}
            input{border: none;padding: 5px;width: 300px;}
            .green,.blue,.orange,form{display: inline-block;margin: 0;padding: 0;}
            .green{color: #a6e22e;}
            .blue{color: #ae81ff;}
            .orange{color: #e6db74;}
        </style>
    </head>

    <body>
        <p>
            Hey! I'm <a class="orange" href="https://www.facebook.com/ferlisi.simone">Simone Ferlisi</a> from <a class="orange" href="http://moonjump.it">Moonjump</a>, and this is <a class="green" href="http://jarvis.moonjump.it/yesterday/">Yesterday</a><br>
            I made this little app for <a class="green" href="http://asana.com/">Asana</a> (&hearts;) to see my daily productivity report.<br>
            Hope would be useful for you :)<br><br>
            <span class="blue">A little tip:<br>I use [HH:MM] Before task titles to calculate effective workin' hours, get on it!</span><br>
            =============================================================<br><br>
            So, let's start! Paste here your API key: <span class="blue">(then press Enter)</span><br>
            you can find API key in: Asana>>your name>>Account Settings>>Apps>>Api Key
        </p>

        >> <form action="" method="GET"><input type="text" name="apikey" <?php if(isset($_GET['apikey'])) echo 'value="'.$_GET['apikey'].'"'; else echo 'autofocus'; ?>></form>

        <?php if(isset($_GET['apikey'])){ ?>
            
            <p>
                <br>
                <span class="orange">Cool!</span> Now you should select your workspace from below<br>
                The next step is too slow, have you any suggestion for that?<br><br>
                <span class="blue">Wait, did you know you can see all your team report? Try it!</span><br>
                =============================================================<br><br>
            </p>

            <?php 
                require('api/asana.php');
                $asana = new Asana(array('apiKey' => $_GET['apikey'])); // API Key, later will be OAuth

                $workspaces = json_decode($asana->getWorkspaces());
                foreach ($workspaces->data as $ws) echo '| <a class="blue" href="?apikey='.$_GET['apikey'].'&wid='.$ws->id.'&wnm='.$ws->name.'">'.$ws->name.'</a> |';
            ?>

        <?php } ?>

        <?php if(isset($_GET['wid'])){ ?>
            <p>
                if you have any suggestion please contact me at <a href="mailto:simone@moonjump.it">simone@moonjump.it</a>
            </p>
            

            <?php
                $workspace->id = $_GET['wid']; 
                $workspace->name = $_GET['wnm'];

                $users_to_display = array();

                echo '<br><br><span class="blue">You selected a new Workspace >> '.$workspace->name.'</span><br>';
                
                $users = json_decode($asana->getUsers());
                foreach($users->data AS $user)
                {
                    $in_workspace=false;
                    $user = json_decode($asana->getUserInfo($user->id));

                    foreach($user->data->workspaces AS $u_workspace)
                    {
                        if($u_workspace->id == $workspace->id) 
                        {
                            $in_workspace=true;
                            break;
                        }
                    }

                    if($in_workspace) $users_to_display[] = array('id' => $user->data->id,'name' => $user->data->name);
                }

                foreach ($users_to_display as $assignee)
                {
                    $task_total = 0;
                    $time_total = 0;

                    echo '<br><br><span class="orange">'.$assignee['name'].'\'s tasks completed since yesterday</span><br>';

                    $filter = array('assignee' => $assignee['id'], 'project' => '', 'workspace' => $workspace->id);
                    $opt = array('completed_since' => '2014-07-15T00:00:00.000Z'); // Set to yesterday!
                    $tasks =  json_decode($asana->getTasksByFilter($filter,$opt));

                    if ($asana->responseCode != '200' || is_null($tasks)) {
                        echo 'Error while trying to connect to Asana, response code: ' . $asana->responseCode;
                        continue;
                    }

                    
                    foreach ($tasks->data as $task)
                    {
                        $task = json_decode($asana->getTask($task->id));

                        if($task->data->completed == 1) // Yesterday
                        {
                            $task_total++;

                            // I'm splitting time brackets from task name, structured like this: [01:45] Buy the milk
                            $name = explode('] ', $task->data->name, 2);
                            $time_total = hms_sum($time_total,ltrim($name[0], '['));

                            // Check out for critical!
                            // Check out for deadlines!

                            echo '&#x2713; '.$name[1].'<br>';

                        }
                    }

                    echo '<span class="blue">=============================================================<br>'
                         .$task_total.' tasks completed. Working Time: '.$time_total.'<br>'
                         .'=============================================================</span><br>';
                }

                /*  JUST A NOTE
                    stdClass Object ( [data] => stdClass Object ( [id] => 14731503300751 [created_at] => 2014-07-15T12:04:56.249Z [modified_at] => 2014-07-15T12:55:57.116Z [name] => [10m] Call Lair [notes] => [assignee] => stdClass Object ( [id] => 7327437058610 [name] => Simone Ferlisi ) [completed] => 1 [assignee_status] => inbox [completed_at] => 2014-07-15T12:55:56.004Z [due_on] => 2014-07-15 [projects] => Array ( ) [tags] => Array ( ) [workspace] => stdClass Object ( [id] => 7327437060665 [name] => Moonjump ) [followers] => Array ( [0] => stdClass Object ( [id] => 7327437058610 [name] => Simone Ferlisi ) ) [parent] => stdClass Object ( [id] => 14731503300695 [name] => Snamid ) ) )
                */
            ?>

        <?php } ?>
    </body>
</html>

<?php 
    function hms_sum($time1, $time2)
    {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time)
        {
            list($hour,$minute,$second) = explode(':', $time);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            $seconds += $second;
        }
        $hours = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);
        $seconds -= $minutes*60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
?>