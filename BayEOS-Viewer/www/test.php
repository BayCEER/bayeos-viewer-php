<!DOCTYPE html>
<html><head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>BayEOS ServerViewer</title>
		
		
<meta name="layout" content="main">
<link rel="stylesheet" href="css/signin.css">
		
		
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/bootstrap.icon-large.min.css">
		<link rel="stylesheet" href="css/main.css">
		<script  type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="/javascript/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" href="/javascript/jquery-ui/css/smoothness/jquery-ui.min.css">
<script type="text/javascript" src="js/jquery.datetimeentry.pack.js"></script>
<script type="text/javascript" src="/javascript/jquery-ui/jquery.mousewheel.js"></script>
<style type="text/css">@import "js/jquery.datetimeentry.css";</style> 


</head>
<body>
	<div id="wrap">
		<nav class="navbar navbar-default">
	<div class="container">		
		<div class="navbar-header">
		
			<button type="button" class="navbar-toggle" data-toggle="collapse"		data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span> <span
					class="icon-bar"></span> <span class="icon-bar"></span> <span
					class="icon-bar"></span>
			</button>
		
		<a href="/gateway/board/index" class="navbar-brand"><strong>BayEOS</strong> Gateway</a>			 			
		</div>
		
		<div class="collapse navbar-collapse navbar-ex1-collapse">
		
			<ul class="nav navbar-nav">
				<li class="active">
					<a href="/gateway/board/index"><span class="glyphicon glyphicon-hdd"></span> Boards</a>
				</li>
				<li >
					<a href="/gateway/boardGroup/index"><span class="glyphicon glyphicon-flag"></span> Groups</a>
				</li>
				<li class="dropdown"><a href="#" class="dropdown-toggle"
					data-toggle="dropdown"><span class="glyphicon glyphicon-wrench"></span>
						Admin<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li >
							<a href="/gateway/boardTemplate/index"><span class="glyphicon glyphicon-hdd"></span> Board Templates</a>
						</li>
						
						<li >
							<a href="/gateway/user/index"><span class="glyphicon glyphicon-user"></span> Users</a>
						</li>

						<li class="divider"></li>

						<li class="dropdown-submenu"><a href="#"><span
								class="glyphicon glyphicon-resize-small"></span> Aggregate</a>
							<ul class="dropdown-menu">
								<li >
									<a href="/gateway/function/index"> Functions</a>
								</li>
								<li >
									<a href="/gateway/interval/index"> Intervals</a>
								</li>
							</ul></li>
						<li >
							<a href="/gateway/spline/index"><span class="glyphicon glyphicon-resize-horizontal"></span> Splines</a>
						</li>
						<li >
							<a href="/gateway/unit/index"><span class="glyphicon glyphicon-tag"></span> Units</a>
						</li>
						<li class="dropdown-submenu"><a href="#"><span
								class="glyphicon glyphicon-time"></span> Jobs</a>
							<ul class="dropdown-menu">
								<li >
									<a href="/gateway/exportJob/edit"> Export</a>
								</li>
								<li >
									<a href="/gateway/deleteJob/edit"> Delete</a>
								</li>
							</ul></li>
						<li >
							<a href="/gateway/fileUpload/upload">
							<span class="glyphicon glyphicon-upload"></span> Upload</a>
						</li>
						<li >
							<a href="/gateway/file/show?file=%2Fvar%2Flog%2Ftomcat6%2Fgateway.log&amp;title=Gateway+Log"><span class="glyphicon glyphicon-file"></span>
								Log</a>
						</li>

					</ul></li>
			</ul>
		
			<div class="navbar-right">
				
					<p class="navbar-text">Signed in as admin </p>													
					<a href="/gateway/logout/index">
					<button type="button" class="btn btn-default navbar-btn">Logout</button>
					</a>
											
			</div>
		</div>

	</div>


</nav>
	<div class="container">
<ol class="breadcrumb"><li class="active">All Folders</li></ol>	
	</div><div class="container">
		<table class="table table-hover col-sm-12">
		<thead>
		<tr>
		<th>Name</th>
		<th class="hidden-xs">Records from</th>
		<th class="hidden-xs">Records until</th>
		</tr>
		</thead>
		<tbody>
	<tr><td>
			<span class="glyphicon glyphicon-folder-close">
			</span> <a href="?id=200107">gateway</a>
					</td>
			<td class="hidden-xs">27.05.2014 21:20</td>
			<td class="hidden-xs">14.06.2014 18:45</td>
			<td class="link">
			<a href="./?edit=200107" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-edit"></span> Details</a>	
			</td>
			</tr>
			<tr><td>
			<span class="glyphicon glyphicon-folder-close">
			</span> <a href="?id=200111">Stromzähler-Server</a>
					</td>
			<td class="hidden-xs">27.05.2014 21:20</td>
			<td class="hidden-xs">15.06.2014 09:46</td>
			<td class="link">
			<a href="./?edit=200111" class="btn btn-xs btn-default">
					<span class="glyphicon glyphicon-edit"></span> Details</a>	
			</td>
			</tr>
					 </tbody>
	     </table> 
	 </div>  
	<div class="container">	
	
	<div class="block-action">
	<a href="?edit=messung_ordner">
	<button type="submit" class="btn btn-primary">
	<span class="glyphicon glyphicon-plus"></span> New
	</button>
	</a>
	<a href="?tab=Search">
	<button type="submit" class="btn btn-primary">
	<span class="glyphicon glyphicon-search"></span> Search
	</button>
	</a>
	
				<a href="?treefilter=1">
	<button type="submit" class="btn btn-primary">
	<span class="glyphicon glyphicon-minus"></span> Hide inactive
	</button>
	</a>
			
			
	</div></div>
<script src="js/bundle-bundle_bootstrap_defer.js" type="text/javascript"></script>
	</div>
	<div id="footer">
  
   <p class="text-muted credit">
   © 2014 <a href="http://www.uni-bayreuth.de/">University of Bayreuth,</a> 
   <a href="http://www.bayceer.uni-bayreuth.de/">BayCEER</a>	Release 0.1.0 - 
   Fabulous icons from <a href="http://glyphicons.com">Glyphicons Free</a>, licensed under <a href="http://creativecommons.org/licenses/by/3.0/">CC BY 3.0</a>
   </p>  
</div>

</body></html>

<?php
?>