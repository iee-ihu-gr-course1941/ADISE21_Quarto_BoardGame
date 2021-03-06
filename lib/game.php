<?php 

require_once "../lib/users.php";
    
    function show_status(){
        header('Content-type: application/json');
        global $mysqli;

        check_abort();
        
        $sql='select * from game_status';
        $st=$mysqli->prepare($sql);
        $st->execute();
        $res=$st->get_result();

        print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
    }


    function check_abort() {
        global $mysqli;
        
        $sql = "update game_status set status='aborded',result=if(p_turn='1','2','1'), p_turn=null, selected_piece=null where p_turn is not null and last_change<(now()-INTERVAL 10 MINUTE) and status='started'";
        $st = $mysqli->prepare($sql);
        $st->execute();
        
    } 

    

    function update_game_status() {
        global $mysqli;
        
        $sql = 'select * from game_status';
        $st = $mysqli->prepare($sql);
    
        $st->execute();
        $res = $st->get_result();
        $status = $res->fetch_assoc();
        
        
        $new_status=null;
        $new_turn=null;
        
        $st3=$mysqli->prepare('select count(*) as aborted from players WHERE last_action< (NOW() - INTERVAL 10 MINUTE)');
        $st3->execute();
        $res3 = $st3->get_result();
        $aborted = $res3->fetch_assoc()['aborted'];
        if($aborted>0) {
            $sql = "UPDATE players SET username=NULL, token=NULL WHERE last_action< (NOW() - INTERVAL 10 MINUTE)";
            $st2 = $mysqli->prepare($sql);
            $st2->execute();
        }
    
        
        $sql = 'select count(*) as c from players where username is not null';
        $st = $mysqli->prepare($sql);
        $st->execute();
        $res = $st->get_result();
        $active_players = $res->fetch_assoc()['c'];
        
        
        switch($active_players) {
            case 0: $new_status='not active'; break;
            case 1: $new_status='initialized'; break;
            case 2: $new_status='started'; 
                    if($status['p_turn']==null) {
                        $new_turn='1'; // It was not started before...
                    }
                    break;
        }
    
        $sql = 'update game_status set status=?, p_turn=?';
        $st = $mysqli->prepare($sql);
        $st->bind_param('ss',$new_status,$new_turn);
        $st->execute();
    }

    function read_status()
{
	global $mysqli;
	$sql = 'select * from game_status';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$status = $res->fetch_assoc();
	return ($status);
}

    function setResult($id){
        global $mysqli;
        
        $p_turn=null;       
        $sql='update game_status set status="ended", result=?, p_turn=?';
        $st = $mysqli->prepare($sql);
        $st->bind_param('ss',$id,$p_turn);
        $st->execute();
    }
    ?>

