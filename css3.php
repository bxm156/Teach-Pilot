<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>

<style type="text/css">
body
{
	background-color:#000;	
}
.img{
	float:left;
	-webkit-transition-duration: 0.5s;
	-webkit-transform:scale(0.5);
	-moz-transform:scale(0.5);
	-moz-transition-duration: 0.5s;
	-o-transform:scale(0.5);
	-o-transition-duration: 0.5s;
	}
.img img{
	padding:10px;
	border:1px solid #fff;
}
.img:hover{
	-webkit-transform:scale(0.8);
	-webkit-box-shadow:0px 0px 30px #ccc;
	-moz-transform:scale(0.8);
	-moz-box-shadow:0px 0px 30px #ccc;
	-o-transform:scale(0.8);
	-o-box-shadow:0px 0px 30px #ccc;
}
.img .mask{
	width: 100%;
	background-color: rgb(0, 0, 0);
	position: absolute;
	height: 100%;
	opacity:0.6;
	cursor:pointer;
	-webkit-transition-duration: 0.5s;
	-moz-transition-duration: 0.5s;
	-o-transition-duration: 0.5s;
	-o-transition-duration: 0.5s;
}
#img-1:hover .mask{
	height:0%;
}
#img-2:hover .mask{
	height:0%;
	margin-top:130px;
}
#img-3 #mask-1 {
	width:50%;
}
#img-3 #mask-2{
	width:50%;
	margin-left:211px;
}
#img-3:hover #mask-1{
	width:0%;
}
#img-3:hover #mask-2{
	margin-left:430px;
	width:0%;
}
#img-4:hover .mask{
	margin-left:219px;
	margin-top:135px;
	height:0%;
	width:0%;
}
#img-5:hover .mask{
	margin-left:219px;
	margin-top:135px;
	height:0%;
	width:0%;
	-webkit-transform: rotateX(360deg);
	-moz-transform: rotate(360deg);
	-o-transform: rotateX(360deg);
	-o-transform: rotate(360deg);
}
#img-6:hover .mask{
	margin-left:219px;
	margin-top:135px;
	height:0%;
	width:0%;
	-webkit-transform: rotateZ(750deg);
	-moz-transform: rotate(750deg);
	-o-transform: rotateZ(750deg);
	-o-transform: rotate(750deg);
}

</style>
</head>

<body>
<div class='img' id='img-1'>
	<div class='mask'></div>
	<a href="#1"><img src='css3/1.jpg' /></a>
</div>
<div class='img' id='img-2'>
	<div class='mask'></div>
	<a href="#2"><img src='css3/2.jpg' /></a>
</div>
<div class='img' id='img-3'>
	<div class='mask' id='mask-1'></div>
	<div class='mask' id='mask-2'></div>
	<a href="#3"><img src='css3/3.jpg' /></a>
</div>
<div class='img' id='img-4'>
	<div class='mask'></div>
	<a href="#4"><img src='css3/4.jpg' /></a>
</div>
<div class='img' id='img-5'>
	<div class='mask'></div>
	<a href="#5"><img src='css3/5.jpg' /></a>
</div>
<div class='img' id='img-6'>
	<div class='mask'></div>
	<a href="#6"><img src='css3/6.jpg' /></a>
</div>
</body>
</html>