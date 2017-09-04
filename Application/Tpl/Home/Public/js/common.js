
 $(function(){
	$(".op-button-wrap_order").click(function(){
		$(".op-wrap-Popup").show();
		$(".bill_list_footer a").click(function(){
			var html = $(this).html();
			if(html=="取消"){
				$(".op-wrap-Popup").css("display","none");
			}
			else{
				window.location.href="billDetail.html"	
			}
		})		
	})
	$(".op-button-wrap_create").click(function(){
		window.location.href="billDetail.html"
	})
	$(".bill_list_search_reset").on("click",function(){
		$(".bill_list_search ul li select").html("<option value='业务类型'>业务类型</option>");
		$(".bill_list_search ul li input[name='bill_list_search_name']").val("");
		$(".bill_list_search ul li input[name='bill_list_search_purOd']").val("");
		$(".bill_list_search ul li input[name='bill_list_search_data']").val("");
	})
	$(".bill_list_table_checkbox_all").on("click",function(){
		var checked=$(this).prop("checked");
		if(checked){
			$(this).prop("checked",true);
		    $(".bill_list_table_checkbox").prop("checked",true)
		}
		else{
			$(this).prop("checked",false);
			$(".bill_list_table_checkbox").prop("checked",false)
		}
	})
	$(".bill_list_table_checkbox").on("click",function(){
		var index=0;
		$(".bill_list_table_checkbox").each(function(){
			var checkIsChecked=$(this).prop("checked");
			if(checkIsChecked){
				$(this).prop("checked",true);
			}
			else{
				$(this).prop("checked",false);
				index++;
			}	
		})
		if(index==0){
			$(".bill_list_table_checkbox_all").prop("checked",true)
		}
		else{
			$(".bill_list_table_checkbox_all").prop("checked",false)
		}
	})
	$(".payable_list_search_reset").click(function(){
		
	})
	
	
	
})