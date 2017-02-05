<?php

include('inc/lib_autolink.php');

function pageHeader() {
	$page_title = TINYIB_PAGETITLE;
	$return = <<<EOF
<!DOCTYPE html>
<html>
	<head>
		<title>{$page_title}</title>
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css" />
		<link rel="stylesheet" type="text/css" href="inc/style.css" />
		<link rel="alternate" type="application/rss+xml" href="rss.php" />
		<meta http-equiv="content-type" content="text/html;charset=UTF-8">
		<script src="inc/script.js" type="text/javascript"></script>		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<link rel="stylesheet" href="http://www.w3schools.com/lib/w3-theme-grey.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		
		<style>
		  video {
            max-width: 100%;
            height: auto;
          }
          iframe,
          embed,
          object {
            max-width: 100%;
          }
		</style>
	</head>
EOF;
	return $return;
}

function pageFooter() {
	$footer = file_get_contents('inc/footer.html');
	$self = $_SERVER['QUERY_STRING'];
	return <<<EOF
		<a class="w3-btn-floating w3-blue" href="#"><i class="fa fa-angle-double-up"></i></a>
		<!-- <a class="w3-btn-floating" href="?$self" target="shoutbox"><i class="fa fa-arrows-alt"></i></a> -->
		$footer
		</div>
		
		<script src="fitvidsjquery.fitvids.js"></script>
		<script>
		  $(document).ready(function(){
			// Target your .container, .wrapper, .post, etc.
			$(".post-message").fitVids();
		  });
		</script>
		<script>
		function myFunction() {
			var x = document.getElementById("Demo");
			if (x.className.indexOf("w3-show") == -1) {
				x.className += " w3-show";
			} else { 
				x.className = x.className.replace(" w3-show", "");
			}
		}
		</script>
	</body>
</html>
EOF;
}

function buildPost($post, $isrespage) {
	$return = "";
	$threadid = ($post['parent'] == 0) ? $post['id'] : $post['parent'];
	$postlink = '?do=thread&id='.$threadid.'#'.$post['id'];
	
	$image_desc = '';
	if ($post['file'] != '') {
		$image_desc =
			cleanString($post['file_original']) .' ('.$post["image_width"].'x'.
			$post["image_height"].', '.$post["file_size_formatted"].')'
		;
	}

	if ($post['parent'] == 0 && !$isrespage) {
		$note = isLocked($threadid) ? '<em>(locked)</em>' : ''; //&#x1f512;
		$return .=
			"<span class=\"replylink\">${note}&nbsp;<a title=\"View thread\" class=\"w3-btn-floating w3-blue\" href=\"?do=thread&id=${post["id"]}\"><i class=\"fa fa-mail-reply\"></i></a></span>"
		;
	}
	
	if ($post["parent"] != 0) {
		$return .= <<<EOF
		
<table>
	<tbody>
		<tr>
			<td class="doubledash">&nbsp;</td>
			<td class="reply w3-round-large" id="reply${post["id"]}">
			
			
EOF;
	} elseif ($post["file"] != "") {
		$return .= <<<EOF
		
	
	<img title="$image_desc" class="thumb" src="thumb/${post["thumb"]}" style="cursor:zoom-in" onclick="document.getElementById('modal${post['id']}').style.display='block'" alt="${post["id"]}" width="${post["thumb_width"]}" height="${post["thumb_height"]}">

		<div id="modal${post['id']}" class="w3-modal" onclick="this.style.display='none'">
			<span class="w3-closebtn w3-hover-red w3-container w3-padding-16 w3-display-topright">&times;</span>
			<div class="w3-modal-content w3-animate-zoom">
				<img src="src/${post['file']}" style="width:100%">
			</div>
		</div>
		
<!--		
<a target="_blank" href="src/${post["file"]}" data-featherlight="image">
	<span id="thumb${post['id']}"><img title="$image_desc" src="thumb/${post["thumb"]}" alt="${post["id"]}" class="thumb" width="${post["thumb_width"]}" height="${post["thumb_height"]}"></span>
</a>
-->

EOF;
	}
	
	$return .= <<<EOF
<a id="${post['id']}"></a>

EOF;

	if ($post["subject"] != "") {
		$return .= " <h1><span class=\"filetitle\">".stripslashes($post["subject"])."</span></h1> ";
	}
	
	$return .= <<<EOF
<a href="?do=delpost&id={$post['id']}" title="Delete"><i class="fa fa-trash"></i></a>
${post["nameblock"]}
EOF;

	if (IS_ADMIN) {
		$return .= ' [<a href="?do=manage&p=bans&bans='.urlencode($post['ip']).'" title="Ban poster">'.htmlspecialchars($post['ip']).'</a>]';
	}

	$return .= <<<EOF
<span class="reflink">
	<a href="$postlink">No.</a><a href="javascript:quote('${post["id"]}')">${post['id']}</a>
</span>

EOF;
	
	
	if ($post['parent'] != 0 && $post["file"] != "") {
		$return .= <<<EOF
<br>

<img title="$image_desc" class="thumb" src="thumb/${post["thumb"]}" style="cursor:zoom-in" onclick="document.getElementById('modal${post['id']}').style.display='block'" alt="${post["id"]}" width="${post["thumb_width"]}" height="${post["thumb_height"]}">

		<div id="modal${post['id']}" class="w3-modal" onclick="this.style.display='none'">
			<span class="w3-closebtn w3-hover-red w3-container w3-padding-16 w3-display-topright">&times;</span>
			<div class="w3-modal-content w3-animate-zoom">
				<img src="src/${post['file']}" style="width:100%">
			</div>
		</div>

<!--		
<a target="_blank" href="src/${post["file"]}"  data-featherlight="image">
	<span id="thumb${post["id"]}"><img title="$image_desc" src="thumb/${post["thumb"]}" alt="${post["id"]}" class="thumb" width="${post["thumb_width"]}" height="${post["thumb_height"]}"></span>
</a>
-->

EOF;
	}

	
	$html = stripslashes($post['message']);	
	$html = autolink($html, 100);
	$post['message'] = $html;
	
	$return .= <<<EOF
<blockquote class="post-message w3-large">
{$post['message']}
</blockquote>

EOF;

	if ($post['parent'] == 0) {
		if (!$isrespage && $post["omitted"] > 0) {
			$return .=
				'<span class="omittedposts">'.$post['omitted'].' post(s) omitted. '.
				'<a href="?do=thread&id='.$post["id"].'">Click here</a> to view.</span>'
			;
		}
	} else {
		$return .= <<<EOF
			</td>
		</tr>
	</tbody>
</table>

EOF;
	}
	
	return $return;
}

function buildPostBlock($parent) {
	$body = '
		<div class="postarea">
		<div class="w3-contentxx">
			<form name="postform" id="postform" action="?do=post" method="post" enctype="multipart/form-data">
	';
	
	if (! $parent) {
		$body .= '
			
			<!-- <input class="w3-row w3-input w3-border w3-round w3-col l6 m6 s12" type="text" name="subject" maxlength="75" placeholder="Subject"> -->
		';
	}
	
	$placeholder = ($parent) ? 'Write a comment...' : 'What\'s on your mind? (Image upload required to start thread)';
	
	$body .= '
		<input type="hidden" name="parent" value="'.$parent.'">
		<input class="w3-input w3-border w3-round w3-col l6 m6 s12" type="text" name="name" maxlength="75" placeholder="Name">	
		
		<textarea class="w3-row w3-input w3-border w3-round w3-col l12 m12 s12" name="message" placeholder="'.$placeholder.'"></textarea>		
		
		<input class="w2-row w3-col l7" type="file" name="file" size="35" title="Images may be GIF, JPG or PNG up to 2 MB.">
		<div style="float:right; text-align:right;">
	';
	
	if (TINYIB_USECAPTCHA && !LOGGED_IN) {
		$captcha_key = md5(mt_rand());
		$captcha_expect = md5(TINYIB_CAPTCHASALT.substr(md5($captcha_key),0,4));
		$body .= '
			<input type="hidden" name="captcha_ex" value="'.$captcha_expect.'" placeholder="Verification" />
			
			<img src="inc/captcha_png.php?key='.$captcha_key.'" />
			<input type="text" name="captcha_out" style="width:12em" maxlength="4" placeholder="Verification" />
			
			
		';
	}
	
	$body .= '
	';
	
	$post_button_name = ($parent) ? 'Post Reply' : 'Create Thread';
	$opt_bump_thread = ($parent) ? '<input class="w3-check" type="checkbox" name="bump" id="bump" checked><label class="w3-validate">Bump</label>' : '';
	$opt_modpost = LOGGED_IN ? '<input class="w3-check" type="checkbox" name="modpost" id="modpost"><label>Modpost</label>' : '';
	$opt_rawhtml = LOGGED_IN ? '<input class="w3-check" type="checkbox" name="rawhtml" id="rawhtml"><label>RawHTML</label>' : '';
	$body .= '	
		
			
				<input class="w3-btn w3-orange w3-round" type="submit" value="'.$post_button_name.'">
				'.$opt_bump_thread.'
				'.$opt_modpost.'
				'.$opt_rawhtml.'
			</div>							
			
			</form>
		</div>
		</div>
		<div id="threadtop" style="clear:both;">&nbsp;</div>
	';
	return $body;
}

function buildPage($htmlposts, $parent, $pages=0, $thispage=0) {
	$locked = $parent ? isLocked($parent) : false;
	$returnlink = ''; $pagelinks = '';
	
	if ($parent == 0) {
		$pages = max($pages, 0);
		
		$pagelinks =
			($thispage == 0) ?
			"&nbsp;<span class=\"w3-btn-floating w3-light-grey\">&lt;</span>&nbsp;" :
			'&nbsp;<a title="Previous page" class="w3-btn-floating" href="?do=page&p=' .($thispage-1). '">&lt;</a>&nbsp;'
		;		
		for ($i = 0;$i <= $pages;$i++) {
			$page = $i + 1;
			$pagelinks .= ($thispage == $i) ? "&nbsp;<span class=\"w3-btn-floating w3-light-grey\">$page</span>&nbsp;" : "&nbsp;<a class=\"w3-btn-floating\" href=\"?do=page&p=$i\">$page</a>&nbsp;";
		}		
		$pagelinks .= ($pages <= $thispage) ?
			"&nbsp;<span class=\"w3-btn-floating w3-light-grey\">&gt;</span>&nbsp;" :
			'&nbsp;<a title="Next page" class="w3-btn-floating" href="?do=page&p='.($thispage+1). '">&gt;</a>&nbsp;'
		;
		
	} else {
		$returnlink = '<span class="replylink"><a title="Return" class="w3-btn-floating w3-blue" href="?"><i class="fa fa-home"></i></a>';
		if (LOGGED_IN) {
			if ($locked) {
				$returnlink .= '&nbsp<a title="Unlock thread" class="w3-btn-floating w3-red" href="?do=lock&id='.$parent.'"><i class="fa fa-lock"></i></a>';
			} else {
				$returnlink .= '&nbsp;<a title="Lock thread" class="w3-btn-floating w3-green" href="?do=lock&id='.$parent.'"><i class="fa fa-unlock"></i></a>';				
			}
		}
		$returnlink .= '</span>
		';
	}
	
	$body = '
	<body onLoad="onFirstLoad();">
		
		<div class="w3-container">
		<h3>'.TINYIB_LOGO.' '.TINYIB_PAGETITLE.'</h3>
		
	';
	if ($locked) {
		$body .= '<div class="replymode w3-red w3-center">This thread is locked. You can\'t reply any more.</div>';
	}
	if ($parent) {
		$body .= $returnlink . "\n" . $htmlposts;
	}
	if (!$locked) {
		$body .= buildPostBlock($parent);
	}
	if (!$parent) {
		$body .= $returnlink . "\n" . $htmlposts;
	}

	$body .= <<<EOF
		<div class="adminbar">
			
		</div>
		<div class="pagelinks">
			$pagelinks
		</div>
		<br>
EOF;

	return pageHeader() . $body . pageFooter();
}

function viewPage($pagenum) {
	$page = intval($pagenum);
	$pagecount = max(0, ceil(countThreads() / TINYIB_THREADSPERPAGE) - 1);
	if (!is_numeric($pagenum) || $page < 0 || $page > $pagecount) fancyDie('Invalid page number.');
	
	$htmlposts = array();
	
	$threads = getThreadRange(TINYIB_THREADSPERPAGE, $pagenum * TINYIB_THREADSPERPAGE );
	
	foreach ($threads as $thread) {
	
		$replies = latestRepliesInThreadByID($thread['id']);
		
		$htmlreplies = array();
		foreach ($replies as $reply) {
			
			$htmlreplies[] = buildPost($reply, False);
		}
		
		$thread["omitted"] = (count($htmlreplies) == 3) ? (count(postsInThreadByID($thread['id'])) - 4) : 0;
		
		$htmlposts[] = buildPost($thread, false) . implode("", array_reverse($htmlreplies)) . "<br style=\"clear:both;\">\n";
	}
	
	return buildPage(implode('', $htmlposts), 0, $pagecount, $page);
}

function viewThread($id) {
	$htmlposts = array();
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) $htmlposts[] = buildPost($post, True);
	$htmlposts[] = "<br style=\"clear:both;\">\n";
	
	return buildPage(implode('',$htmlposts), $id);
}

function adminBar() {
	if (! LOGGED_IN) { return '[<a href="?">Return</a>]'; }
	$text = IS_ADMIN ? '[<a href="?do=manage&p=bans">Bans</a>] ' : '';
	$text .=
		'[<a href="?do=manage&p=threads">Thread list</a>] '.
		'[<a href="?do=manage&p=moderate">Moderate Post</a>] '.
		'[<a href="?do=manage&p=logout">Log Out</a>] '.
		'[<a href="?">Return</a>]'
	;
	return $text;
}

function managePage($text) {
	$adminbar = adminBar();
	$body = <<<EOF
	<body>
		<div class="adminbar">
			$adminbar
		</div>
		<div class="logo">
EOF;
	$body .= TINYIB_LOGO . <<<EOF
		</div>
		<hr>
		<div class="replymode w3-red w3-center">Manage mode</div>
		$text
		<hr>
EOF;
	return pageHeader() . $body . pageFooter();
}

function manageLogInForm() {
	return <<<EOF
	<form id="tinyib" name="tinyib" method="post" action="?do=manage&p=home">
		<fieldset>
			<legend align="center">Please enter an administrator or moderator password</legend>
			<div class="login">
				<input type="password" id="password" name="password" autofocus><br>
				<input class="w3-btn w3-round" type="submit" value="Submit" class="managebutton">
			</div>
		</fieldset>
	</form>
	<br/>
EOF;
}

function manageBanForm() {
	$banstr = isset($_GET['bans']) ? $_GET['bans'] : '';

	return <<<EOF
	<form id="tinyib" name="tinyib" method="post" action="?do=manage&p=bans">
		<fieldset>
			<legend>Ban an IP address from posting</legend>
			<label for="ip">IP Address:</label>
			<input type="text" name="ip" id="ip" value="$banstr" autofocus>
			<input class="w3-btn w3-round" type="submit" value="Submit" class="managebutton">
			<br/>
			<label for="expire">Expire(sec):</label>
			<input type="text" name="expire" id="expire" value="0">&nbsp;&nbsp;
			<small>
				<a href="#" onclick="document.tinyib.expire.value='3600';return false;">1hr</a>&nbsp;
				<a href="#" onclick="document.tinyib.expire.value='86400';return false;">1d</a>&nbsp;
				<a href="#" onclick="document.tinyib.expire.value='172800';return false;">2d</a>&nbsp;
				<a href="#" onclick="document.tinyib.expire.value='604800';return false;">1w</a>&nbsp;
				<a href="#" onclick="document.tinyib.expire.value='1209600';return false;">2w</a>&nbsp;
				<a href="#" onclick="document.tinyib.expire.value='2592000';return false;">30d</a>&nbsp;
				<a href="#" onclick="document.tinyib.expire.value='0';return false;">never</a>
			</small>
			<br/>
			<label for="reason">Reason:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
			<input type="text" name="reason" id="reason">&nbsp;&nbsp;<small>(optional)</small>
		</fieldset>
	</form>
	<br/>
EOF;
}

function manageBansTable() {
	$text = '';
	$allbans = allBans();
	if (count($allbans) > 0) {
		$text .= '<table><tr><th>IP Address</th><th>Set At</th><th>Expires</th><th>Reason Provided</th><th>&nbsp;</th></tr>';
		foreach ($allbans as $ban) {
			$expire = ($ban['expire'] > 0) ? date('y/m/d(D)H:i:s', $ban['expire']) : 'Never';
			$reason = ($ban['reason'] == '') ? '&nbsp;' : htmlentities($ban['reason']);
			$text .= '<tr><td>' . $ban['ip'] . '</td><td>' . date('y/m/d(D)H:i:s', $ban['timestamp']) . '</td><td>' . $expire . '</td><td>' . $reason . '</td><td><a href="?do=manage&p=bans&lift=' . $ban['id'] . '">lift</a></td></tr>';
		}
		$text .= '</table>';
	}
	return $text;
}

function manageModeratePostForm() {
	return <<<EOF
	<form id="tinyib" name="tinyib" method="get" action="?">
		<input type="hidden" name="manage" value="">
		<fieldset>
			<legend>Moderate a post</legend>
			<input type="hidden" name="do" value="manage">
			<input type="hidden" name="p" value="moderate">
			<label for="moderate">Post ID:</label>
			<input type="text" name="moderate" id="moderate" autofocus>
			<input class="w3-btn w3-round" type="submit" value="Submit" class="managebutton">
			<br/>
		</fieldset>
	</form>
	<br/>
EOF;
}

function manageModeratePost($post) {
	$ban = banByIP($post['ip']);
	$ban_disabled = (!$ban && IS_ADMIN) ? '' : ' disabled';
	$ban_disabled_info = (!$ban) ? '' : (' A ban record already exists for ' . $post['ip']);
	$post_html = buildPost($post, true);
	$post_or_thread = ($post['parent'] == 0) ? 'Thread' : 'Post';
	return <<<EOF
	<fieldset>
		<legend>Moderating post No.${post['id']}</legend>		
		<div class="floatpost">
			<fieldset>
				<legend>$post_or_thread</legend>	
				$post_html
			</fieldset>
		</div>		
		<fieldset>
			<legend>Action</legend>					
			<form method="get" action="?">
				<input type="hidden" name="do" value="manage" />
				<input type="hidden" name="p" value="delete" />
				<input type="hidden" name="delete" value="${post['id']}" />
				<input class="w3-btn w3-round" type="submit" value="Delete $post_or_thread" class="managebutton" />
			</form>
			<br/>
			<form method="get" action="?">
				<input type="hidden" name="do" value="manage" />
				<input type="hidden" name="p"  value="bans" />
				<input type="hidden" name="bans" value="${post['ip']}" />
				<input class="w3-btn w3-round" type="submit" value="Ban Poster" class="managebutton"$ban_disabled />$ban_disabled_info
			</form>
		</fieldset>	
	</fieldset>
	<br />
EOF;
}

function manageAllThreads() {
	$threads = getThreadRange(10000, 0);
	$locks   = getAllLocks();
	
	$ret = '
		<table style="width:100%;border:0px;border-collapse:collapse;margin:2px;">
			<thead style="background-color:darkred;color:white;text-align:left;">
				<tr>					
					<th>#</th>
					<th>Subject</th>
					<th>First post</th>
					<th style="width:160px;">Created</th>
					<th style="width:160px;">Last Bump</th>
					<th>Locked</th>
				</tr>
			</thead>
			<tbody>
	';
	foreach($threads as $thread) {
		$locked = in_array($thread['id'], $locks);
		// Workaround for incorrectly imported history
		$bump = ($thread['bumped'] > 1000 ? date(TINYIB_DATEFORMAT,$thread['bumped']) : '-');
		$ret .= '
				<tr>
					<td><a href="?do=thread&id='.$thread['id'].'">#'.$thread['id'].'</a></td>
					<td>'.stripslashes($thread['subject']).'</td>
					<td>'.htmlspecialchars(substr($thread['message'], 0, 60)).'</td>
					<td>'.date(TINYIB_DATEFORMAT, $thread['timestamp']).'</td>
					<td><a href="?do=manage&p=bump&id='.$thread['id'].'" title="Bump this thread">'.$bump.'</a></td>
					<td>'.($locked ? 'Locked' : '-').'</td>
				</tr>
		';
	}
	$ret .= '
			</tbody>
		</table>
	';
	return $ret;
}

