<?php
include('./_config.php');
if(isset($_POST['type']) && $_POST['type']!='' && isset($_POST['id']) && $_POST['id']>0){
	$type=mysqli_real_escape_string($conn,$_POST['type']);
	$id=mysqli_real_escape_string($conn,$_POST['id']);
	
	// Safe cookie names (avoid special characters)
	$likeCookieName     = 'like_'.md5($id);
	$dislikeCookieName  = 'dislike_'.md5($id);
	
	// Make sure the record exists
	mysqli_query($conn, "INSERT INTO `pageview` (id, like_count, dislike_count) VALUES ('$id', 0, 0) ON DUPLICATE KEY UPDATE id=id");
	
	if($type=='like'){
		if(isset($_COOKIE[$likeCookieName])){
			setcookie($likeCookieName,'',time()-3600,'/');
			$sql="UPDATE `pageview` set like_count=like_count-1 where id='$id'";
			$opertion="unlike";
		}else{
			
			if(isset($_COOKIE[$dislikeCookieName])){
				setcookie($dislikeCookieName,'',time()-3600,'/');
				mysqli_query($conn,"UPDATE `pageview` set dislike_count=dislike_count-1 where id='$id'");
			}
			
			setcookie($likeCookieName,'yes',time()+60*60*24*365*5,'/');
			$sql="UPDATE `pageview` set like_count=like_count+1 where id='$id'";
			$opertion="like";
		}
	}
	
	if($type=='dislike'){
		if(isset($_COOKIE[$dislikeCookieName])){
			setcookie($dislikeCookieName,'',time()-3600,'/');
			$sql="UPDATE `pageview` set dislike_count=dislike_count-1 where id='$id'";
			$opertion="undislike";
		}else{
			
			if(isset($_COOKIE[$likeCookieName])){
				setcookie($likeCookieName,'',time()-3600,'/');
				mysqli_query($conn,"UPDATE `pageview` set like_count=like_count-1 where id='$id'");
			}
			
			setcookie($dislikeCookieName,'yes',time()+60*60*24*365*5,'/');
			$sql="UPDATE `pageview` set dislike_count=dislike_count+1 where id='$id'";
			$opertion="dislike";
		}
	}
	mysqli_query($conn,$sql);

	$row=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * from `pageview` where id='$id'"));
	
	echo json_encode([
		'opertion'=>$opertion,
		'like_count'=>$row['like_count'],
		'dislike_count'=>$row['dislike_count']
	]);
}
?>