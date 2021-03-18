<?php
//甜糖token
$token='';
//Server酱
$sms_server='';

function substr_cut($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}
function substr_cuts($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 6, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 4, 'utf-8');
    return $strlen == 7 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 4) : $firstStr . str_repeat("*", $strlen - 7) . $lastStr;
}
?>
<meta charset="UTF-8">
<style>
*{
	padding:0px;
	margin:0px;
	font-size:20px;
	text-align:center;
	border:2px #000 solid;
}
table{ width:100%; }
tr{ width:100%; }
td{ width:50%; }
</style>
<center>
<table>
<?php
$tixian='on';
$caiji='on';

#自动采集
@ $nowtime=date("Y-m-d H:i:s");
echo "<tr><td colspan='2' >".$nowtime."</td></tr>";
if($caiji=='on'){
$curl='curl -H "authorization:'.$token.'" -s "https://tiantang.mogencloud.com/api/v1/devices?page=1&type=2&per_page=64"';
$zone_id0=json_decode(exec($curl), true);
if($zone_id0['errCode']!=0){
	echo "<tr><td colspan='2' >甜糖Token错误</td></tr>";
}else{
	
	#设备列表
	$client_list=$zone_id0['data']['data'];
	$i=0;
	echo "<tr><td colspan='2' >开始采集星愿</td></tr>";
	$client_all='';
	while(!empty($client_list[$i]['id'])){
		#设备ID	  $devId
		$devId=$client_list[$i]['id'];
		#设备名称 $devName
		$devName=$client_list[$i]['alias'];
		#设备产生星愿  $devSore
		$devSore=$client_list[$i]['inactived_score'];
		#设备收取
		$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/api/v1/score_logs?device_id='.$devId.'\&score='.$devSore;
		exec($curl);
		echo "<tr><td>设备：".$devName."</td><td>".$devSore."星愿</td></tr>";
		$client_all=$client_all.urlencode("设备：".$devName.":".$devSore."星愿")."%0D%0A%0D%0A";
		$i++;
	}
	
	#登录签到
	$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/web/api/account/sign_in';
	exec($curl);
	
	#获取推广星愿 $inactivedPromoteScore
	$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/web/api/account/message/loading';
	$inactivedPromoteScore_arr=json_decode(exec($curl), true);
	$inactivedPromoteScore=$inactivedPromoteScore_arr['data']['inactivedPromoteScore'];
	$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/api/v1/promote/score_logs?score='.$inactivedPromoteScore;
	exec($curl);
	#总推广星愿 $promoteScore
	#总共星愿 $score
	#今日星愿 $add_up_score
	$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/web/api/account/message/loading';
	$Score_arr=json_decode(exec($curl), true);
	$promoteScore=$Score_arr['data']['promoteScore'];
	$score=$Score_arr['data']['score'];
	$add_up_score=$Score_arr['data']['add_up_score'];
	echo "<tr><td>推广星愿：</td><td>".$inactivedPromoteScore."星愿</td></tr>";
	echo "<tr><td colspan='2' >往期星愿信息</td></tr>";
	echo "<tr><td>推广星愿：</td><td>".$promoteScore."星愿</td></tr>";
	echo "<tr><td>星愿总额：</td><td>".$add_up_score."星愿</td></tr>";
	echo "<tr><td>剩余星愿：</td><td>".$score."星愿</td></tr>";
	$client_all=$client_all.urlencode("推广星愿:".$inactivedPromoteScore."星愿")."%0D%0A%0D%0A";
	$client_all=$client_all.urlencode("账号星愿:".$score."星愿")."%0D%0A%0D%0A";
	
}
}
#自动提现
if($tixian=='on'){
	#获取星愿 $inactivedPromoteScore
	$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/web/api/account/message/loading';
	$all_arr=json_decode(exec($curl), true);
	if($all_arr['errCode']==1003){
		echo "<tr><td colspan='2' >甜糖Token错误</td></tr>";
	}else{
		#星愿$score
		$score=$all_arr['data']['score'];
		#支付宝账号 $real_name
		$real_name=$all_arr['data']['zfbList'][0]['name'];
		#支付宝ID
		$card_id=$all_arr['data']['zfbList'][0]['account'];
		#开始处理提现
		echo "<tr><td colspan='2' >星愿提现信息</td></tr>";
		//echo "<tr><td>支付宝账号</td><td>".substr_cut($real_name)."</td></tr>";
		echo "<tr><td>支付宝账号</td><td>".substr_cuts($card_id)."</td></tr>";
		$client_all=$client_all.urlencode("支付宝账号：".substr_cuts($card_id))."%0D%0A%0D%0A";
		echo "<tr><td>拥有星愿：</td><td>".$score."星愿</td></tr>";
		$money=floor($score/100);
		if($money>0){
			if($money>99){
				echo "<tr><td>账号总共可提现：".floor($score/100)*100 ."星愿</td><td>一次最高只能提现99元。</td></tr>";
				$money=99;
			}else if($money<99 && $money>=10){
				echo "<tr><td>账号总共可提现：".floor($score/100)*100 ."星愿</td><td>可以提现".$money."元。</td></tr>";
			}else if($money<10){
				echo "<tr><td colspan='2' >账号星愿不足10元，无法提现</td></tr>";
			}
			if(date("N",$time)==3 && $money>=10){
				$url_text="/api/v1/withdraw_logs?score=".$money."&real_name=".$real_name."&card_id=".$card_id."&bank_name=支付宝&sub_bank_name=&type=zfb";
				$curl='curl -X POST -H "authorization:'.$token.'" -s http://tiantang.mogencloud.com/'.$url_text;
				$money_pos=json_decode(exec($curl), true);
				if($money_pos['errCode']==0){
					echo "<tr><td colspan='2' >甜糖自动提现:".$money."元成功！，请留意到账情况！</td></tr>";
					$client_all=$client_all.urlencode("甜糖自动提现:".$money."元成功！，请留意到账情况！")."%0D%0A%0D%0A";
				}else{
					echo "<tr><td colspan='2' >甜糖自动提现失败，具体原因：".$money_pos['msg']."</td></tr>";
					$client_all=$client_all.urlencode("甜糖自动提现失败，具体原因：".$money_pos['msg'])."%0D%0A%0D%0A";
				}
			}else{
				echo "<tr><td colspan='2' >每周三才会开始提现</td></tr>";
			}
		}
	}
}
if(!empty($sms_server)){
	file_get_contents("https://sc.ftqq.com/".$sms_server.".send?desp=".$client_all."&text=".urlencode("甜糖采集通知"));
}
?>
</table>
</center>
