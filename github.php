<?php

$branch = "Automattic/wp-calypso";

function checkoutBranch( $branch ) {
    system( '/usr/local/bin/tmux has-session -t calypso || /usr/local/bin/tmux new-session -d -s calypso' );
    sleep( 1 );
    system( '/usr/local/bin/tmux send -t calypso "^C"' );
    sleep( 3 );
    system( '/usr/local/bin/tmux send -t calypso "cd /Users/Artpi/GIT/calypso && git checkout master && git pull && git checkout '.$branch.' && git pull && make run"' );
    sleep( 1 );
    system( '/usr/local/bin/tmux send -t calypso ENTER' );
}

function curl( $url ) {
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
    $body = curl_exec($ch);
    curl_close($ch);
    return $body;
}

function search( $search ) {
    global $branch;
    $response = curl( "https://api.github.com/search/issues?q=repo:".$branch."+".$search );
    $data = json_decode( $response, true );

    echo '<?xml version="1.0"?><items>';
    while (list($key, $val) = each( $data['items'] ) ) {
        echo '<item uid="'.$val['number'].'" arg="'.$val['html_url'].'"><title>'.preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $val['title'] ).'</title>
    <subtitle>#'.$val['number'].' by @'.$val['user']['login'].'</subtitle>

    </item>';
    }
    echo "</items>";
}


function getBranch( $prUrl ) {
    global $branch;
    $prUrl = str_replace( "github.com/".$branch."/pull", "api.github.com/repos/".$branch."/pulls", $prUrl );
    $data = json_decode( curl( $prUrl ), true );
    return $data['head']['ref'];
}

?>
