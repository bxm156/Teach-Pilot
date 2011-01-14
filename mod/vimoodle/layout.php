<?php 
	if(isset($_GET['videoOverride']) and !empty($_GET['videoOverride']))
	{
		$video = intval($_GET['videoOverride']);		
	}
	$result = $client->jsonRequest("getVideoInfo",array($video,$user,$pin),1);
	$info = json_decode($result,true);
	if(!empty($album)) {
		$result = $client->jsonRequest("getAlbumVideos",array($album,$user,$pin),1);
		$albums = json_decode($result,true);
	}
?>
<div style="margin:25px">
    <div style="float:left;width:640px;height:480">
        <div style="height:520px" align="center">
                <span style="font-size:36px;">
                <?php echo $info['video']['0']['title']; ?>
                </span><br />
            
            <?php 
            $result = $client->jsonRequest("getVideoEmbedHTML",array($video),1);
            $result = json_decode($result,true);
            print(html_entity_decode($result['html']));
            ?>
             
        </div>
    </div>
    <div style="float:left;width:250px;border:solid 1px #626262;min-height:520px;background-color:#CCC;padding:5px;margin-left:10px">
    	<?php if($info['stat'] == "ok") { ?>
        <div align="center">Metadata</div>
        <span style="font-size:14px">
        Title: <?php echo $info['video']['0']['title']; ?><br />
        Number of Plays: <?php echo $info['video']['0']['number_of_plays']; ?><br />
        Duration: <?php echo $info['video']['0']['duration']; ?>s<br />
        <?php } ?>
        </span>
    </div>
</div>
<div style="clear:both;width:628px;border:solid 1px #626262;background-color:#CCC;margin-left:25px;min-height:200px;padding:5px;">
Description:<br />
         <span style="font-size:14px;color:#0a304e">
    	<?php echo $vimoodle->intro; ?>
       
    <br />
	Date Uploaded: <?php echo $info['video']['0']['upload_date']; ?>
     </span>
     <?php if(!empty($album)) { ?>
     <br /><br />
     Other Videos in the same album:<br />
     
     <?php
	 if($albums['stat'] == "ok")
	 {
		$list = $albums['videos']['video'];
		foreach($list as $albumVideo) 
		{
				if($albumVideo['id'] != $video) {
						$result = $client->jsonRequest("getVideoInfo",array($albumVideo['id'],$user,$pin),1);
						$info = json_decode($result,true);
						
					echo "<a href=\"view.php?id={$id}&videoOverride=".$albumVideo['id']."\" border=\"0\" alt=\"".$video['title']."\">";
					echo "<img src=\"".$info['video']['0']['thumbnails']['thumbnail']['0']['_content']."\">";
					//echo $video['title'];
					echo "</a>";
				}
		}
	 }
	 ?>
    <?php } ?>
</div>
