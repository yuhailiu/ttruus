<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="/css/bootstrap-combined.min.css" rel="stylesheet">
	<script src="/js/jquery-1.9.1.js"></script>
	<script src="/js/bootstrap.min.js"></script>
	<script src="/js/bootstrap-paginator.min.js"></script>
	
	<script type="text/javascript">
	$(function(){
		var options ={
				currentPage: 1,
				totalPages: 2
		}

		$('#example').bootstrapPaginator(options);
	});
	</script>
	<title>Untitled</title>
</head>
<body>
    <p>test paginator</p>
    <div id="example"></div>

</body>
</html>
