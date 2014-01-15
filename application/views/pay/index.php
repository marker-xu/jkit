<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 

<html> 

	<head> 

		<title>去支付</title> 

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 

	</head>

	<body>
		<form action="/pay/topay" method="get">
			<table>
				<tr>
				
				<th>
					支付类型
				</th>
				<td>
				<select name="pay_type">
				<?php foreach($type_list as $strType=>$strName):?>
				<option value="<?php echo $strType;?>"><?php echo $strName;?></option>
				<?php endforeach;?>
				</select>
				</td>
				
				</tr>
				<tr>
				
				<th>
					支付金额
				</th>
				<td>
				<input type="text" name="fee" value="0.01" />
				</td>
				
				</tr>
				<tr>
					<td colspan=2><input type="submit" value="提交" /></td>
				</tr>
				
			</table>
			
		</form>
	</body>
</html>
