
//20180328최인석 myoffice 에있는 js 추가함 

//JSON KEY to lowercase
function echoNull2Blank(str) {
	if (str == null) return '';
	return str;
}
function ConvertKeysToLowerCase(obj) {
    if (!obj || typeof obj !== "object") return null;

    if (obj instanceof Array) {
        return $.map(obj, function(value) {
            return ConvertKeysToLowerCase(value);
        });
    }

    // manipulates the object being passed in
    $.each(obj, function(key, value) {
        // delete existing key
        delete obj[key];
        key = key.toLowerCase();
        obj[key] = value;
        ConvertKeysToLowerCase(value);
    });

    return obj;
};

//Pie Chart
function makePieChart(targetId, chartData, valStr, valTitle, valField){
	var chart = AmCharts.makeChart( targetId, {
	  "type": "pie",
	  "theme": "light",
	  "titles": [ {
	    "text": "",
	    "size": 25
	  } ],
	  "dataProvider": chartData,
	  "valueField": valField,
	  "titleField": valTitle,
	  "startEffect": "elastic",
	  "startDuration": 2,
	  "labelRadius": 15,
	  "innerRadius": "15%",
	  "depth3D": 10,
	  "balloonText": "<span style='font-size:13px;color:#333;'>[[title]]<br><b>[[value]]"+valStr+"</b> ([[percents]]%)</span>",
	  "angle": 30,
	  "export": {
	  "enabled": true
	  }
	} );
}

//Bar Chart
function makeChart(targetId, chartData, valStr, valCate, valField){
	var mChart = AmCharts.makeChart( targetId, {
		  "type": "serial",
		  "theme": "light",
		  "dataProvider": chartData,
		  "valueAxes": [ {
		    "gridColor": "#dddddd",
		    "gridAlpha": 0.2,
		    "dashLength": 0
		  } ],
		  "gridAboveGraphs": true,
		  "startDuration": 1,
		  "graphs": [ {
		    "balloonText": "<b style=\"font-size:13px;color:#333;\">[[category]]: [[value]]"+valStr+"</b>",
		    "fillAlphas": 0.8,
		    "lineAlpha": 0.2,
		    "type": "column",
		    "valueField": valField
		  } ],
		  "chartCursor": {
		    "categoryBalloonEnabled": false,
		    "cursorAlpha": 0,
		    "zoomable": false
		  },
		  "categoryField": valCate,
		  "categoryAxis": {
		    "gridPosition": "start",
		    "gridAlpha": 0,
		    "tickPosition": "start",
		    "tickLength": 20
		  },
		  "export": {
		    "enabled": true
		  }

		} );
}

/**
 * 임시form 생성
 */
function cFrm(frm, url, fields, method, encType){
	var cf;
	if(!$("#"+frm).length){
		if(encType){
			cf = $('<form method="POST" id="'+frm+'" name="'+frm+'" enctype="'+encType+'"></form>');
		}else{
			if(method){
				cf = $('<form method="'+method+'" id="'+frm+'" name="'+frm+'"></form>');
			}else{
				cf = $('<form method="POST" id="'+frm+'" name="'+frm+'"></form>');
			}
		}

		if(fields){
			$.each(fields, function(nm, val){
				$(cf).append('<input type="hidden" id="'+nm+'" name="'+nm+'" value="'+val+'">');
			});
		}

		$(cf).css('diaplay', 'none');
	    $(cf).css('top', '-2000px');
	    $(cf).css('left', '-2000px');
	}else{
		cf = $("#"+frm);

		if(fields.length){
			$.each(fields, function(nm, val){
				$('input[name='+nm+']').val(val);
			});
		}
	}

	$(cf).attr("action", url);
	$("body").append(cf);

	return cf;
}

/**
 * 임시form 생성
 */
function cFrmPop(frm, url, fields, target, method, encType){
	var cf;
	if(!$("#"+frm).length){
		if(encType){
			cf = $('<form method="POST" id="'+frm+'" name="'+frm+'" target="'+target+'" enctype="'+encType+'"></form>');
		}else{
			if(method){
				cf = $('<form method="'+method+'" id="'+frm+'" name="'+frm+'" target="'+target+'"></form>');
			}else{
				cf = $('<form method="POST" id="'+frm+'" name="'+frm+'" target="'+target+'"></form>');
			}
		}

		if(fields){
			$.each(fields, function(nm, val){
				$(cf).append('<input type="hidden" id="'+nm+'" name="'+nm+'" value="'+val+'">');
			});
		}

		$(cf).css('diaplay', 'none');
	    $(cf).css('top', '-2000px');
	    $(cf).css('left', '-2000px');
	}else{
		cf = $("#"+frm);

		if(fields.length){
			$.each(fields, function(nm, val){
				$('input[name='+nm+']').val(val);
			});
		}
	}

	$(cf).attr("action", url);
	$("body").append(cf);

	return cf;
}



/**
 * Ajax 파일 다운로드
 * 사용예:$.download('testExcelDownload.php', {fileNm:'test'}, 'post');
 */
jQuery.download = function(url, data, method){
    // url과 data를 입력받음
    if( url && data ){
        // 파라미터를 form의  input으로 만든다.
        var inputs = '';
        $.each(data, function(k,v) {
			inputs+='<input type="hidden" name="' + k + '" value="'+ v +'" />';
	    });
        // request를 보낸다.
        jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
        .appendTo('body').submit().remove();
    };
};


var yearStr = '년';
var monthStr = '월';
var dayStr = '일';
/**
 * 년월일 selectbox생성
 * @param  {[type]} url      [description]
 * @param  {[type]} sync     [description]
 * @param  {[type]} data     [description]
 * @param  {[type]} targetYy [description]
 * @param  {[type]} targetMm [description]
 * @param  {[type]} targetDd [description]
 * @return {[type]}          [description]
 */
function sDate(url, sync, data, targetYy, targetMm, targetDd){
	var defaults = {};
	var params = $.extend(defaults, data);

	var makeDateUrl = url+'makeDate.php';
	var makeDdDateUrl = url+'makeDdDate.php';

	ajaxProc(
			makeDateUrl,
			sync,
			params,
			function(rec, res){
				if(targetYy != null){
					if(targetYy.containsOption("")) targetYy.removeOption(/[^0]/);
					else targetYy.removeOption(/./);

					for ( var i = strToint(res.rtData.ST_YY); i <=strToint(res.rtData.ED_YY) ; i++) {
						if(params.selYy!=undefined) {
							if(i==params.selYy) {
								targetYy.addOption(pad(i,4),pad(i,4) + yearStr,true);
							}else{
								targetYy.addOption(pad(i,4),pad(i,4) + yearStr,false);
							}
						} else {
							if(i== res.rtData.ED_YY) {
								targetYy.addOption(pad(i,4),pad(i,4) + yearStr,true);
							}else{
								targetYy.addOption(pad(i,4),pad(i,4) + yearStr,false);
							}
						}
					}

					targetYy.change(function() {
						if(targetMm) sDateChan(makeDdDateUrl, sync, targetYy.val(), targetMm.val(), targetDd, null);
					});
				}

				if(targetMm!=null) {
					if(targetMm.containsOption("")) targetMm.removeOption(/[^0]/);
					else targetMm.removeOption(/./);

					for ( var i = 1; i <= 12; i++) {
						if(params.selMm!=undefined) {
							if( i==params.selMm) {
								targetMm.addOption(pad(i,2),pad(i,2) + monthStr,true);
							} else {
								targetMm.addOption(pad(i,2),pad(i,2) + monthStr,false);
							}
						} else {
							if( i== res.rtData.NOW_MM) {
								targetMm.addOption(pad(i,2),pad(i,2) + monthStr,true);
							} else {
								targetMm.addOption(pad(i,2),pad(i,2) + monthStr,false);
							}
						}

					}

					if(params.isMmFunc==undefined) {
						targetMm.change(function() {
							sDateChan(makeDdDateUrl, sync, targetYy.val(), targetMm.val(), targetDd, null);
						});
					} else {
						if(!params.isMmFunc) {
							targetMm.change(function() {
								sDateChan(makeDdDateUrl, sync, targetYy.val(), targetMm.val(), targetDd, null);
							});
						}
					}
				}

				if(targetDd!=undefined) {
					if(targetDd.containsOption("")) targetDd.removeOption(/[^0]/);
					else targetDd.removeOption(/./);

					for ( var i = 1; i <= strToint(res.rtData.ED_DD); i++) {
						if(params.selDd!=undefined) {
							if( i==params.selDd) {
								targetDd.addOption(pad(i,2),pad(i,2) + dayStr,true);
							} else {
								targetDd.addOption(pad(i,2),pad(i,2) + dayStr,false);
							}
						} else {
							if( i== res.rtData.NOW_DD) {
								targetDd.addOption(pad(i,2),pad(i,2) + dayStr,true);
							} else {
								targetDd.addOption(pad(i,2),pad(i,2) + dayStr,false);
							}
						}
					}
				}

			}
		);
}

function sDateChan(url, sync, year, month, targetDd, idx) {
	var params = {year:year, month:month};

	ajaxProc(
			url,
			sync,
			params,
			function(rec, res){
				if(targetDd!=undefined) {
				// 초기화
				if(targetDd.containsOption("")) targetDd.removeOption(/[^0]/);
				else targetDd.removeOption(/./);

				var e = strToint(res.rtData.ED_DD);
				var isSel = false;
				for ( var i = 1; i <= e; i++) {
					isSel = false;
					if(idx!=null) {
						if( idx==0 ) {
							if(i==e) isSel = true;
						} else if( i==idx) {
							isSel = true;
						}
					} else {
						if( i== res.rtData.NOW_DD) {
							isSel = true;
						}
					}
					targetDd.addOption(pad(i,2),pad(i,2) + "일", isSel);
				}
			}
		}
	);
}

/**
 * 팝업호출
 * @param  {[type]} url  [description]
 * @param  {[type]} sync [description]
 * @param  {[type]} data [description]
 * @param  {[type]} opt  [description]
 * @return {[type]}      [description]
 */
function cPop(url, sync, data, opt, rm){
	var defaults = {};
	var optDefaults = {left:0, top:0, width:500, height:550,
		mbar:'no', sbar:'no', tbar:'no', stat:'no',
		resiz:'no', loc:'no', popNm:'cPop'};

	var optParams = $.extend(optDefaults, opt);
	var params = $.extend(defaults, data);

	if(opt.left == undefined) optDefaults.left = Math.floor((screen.availWidth-optDefaults.width) / 2);
	if(opt.top == undefined) optDefaults.top = Math.floor((screen.availHeight-optDefaults.height) / 2);

	var popOption = "left=" + optDefaults.left + ",top=" + optDefaults.top + ",width=" + optDefaults.width + ", height=" + optDefaults.height + ",";
	popOption += "menubar=" + optDefaults.mbar + ", scrollbars=" + optDefaults.sbar + ", toolbar=" + optDefaults.tbar + ",";
	popOption += "status=" + optDefaults.stat + ", resizable=" + optDefaults.resiz + ", location=" + optDefaults.loc;

	var newPop = window.open('', optDefaults.popNm, popOption);

	newPop.focus();

	if(newPop = null){
		alert("팝업창이 차단되어 있습니다. 팝업창 호출을 허용해 주십시오.");
		return;
	}
	var cfrm = cFrmPop('cFrmAction', url, params, optDefaults.popNm);
	cfrm.submit();

	if(rm){
		$("#cFrmAction").remove();
	}

	return newPop;
}


/**
 * selectBox 생성
 */
function cSelect(url, sync, data, objList, sel){
	ajaxSelect(
		url,
		sync,
		data,
		function(res){
			if($.isArray(objList)) {
				for(var i=0;i<objList.length;i++) {
					if(objList[i].containsOption("")){
						objList[i].removeOption(/[^0]/);
					}else{
						objList[i].removeOption(/./);
					}
				}
			} else {
				if(objList.containsOption("")){
					objList.removeOption(/[^0]/);
				}else{
					objList.removeOption(/./);
				}
			}

			$.each(res.rtData, function(k,val) {
				if($.isArray(objList)) {
					for(var i=0;i<objList.length;i++) {
						if(sel) {
							if(sel==val.CODE){
								objList[i].addOption(val.CODE,val.VALUE,true);
							}else{
								objList[i].addOption(val.CODE,val.VALUE,false);
							}
						} else {
							objList[i].addOption(val.CODE,val.VALUE,false);
						}
					}
				} else {
					if(sel) {
						if(sel==val.CODE) objList.addOption(val.CODE,val.VALUE,true);
						else objList.addOption(val.CODE,val.VALUE,false);
					} else {
						objList.addOption(val.CODE,val.VALUE,false);
					}
				}
			});
		}
	);
}

var cbCnt = 0;
var cbSucess = null;
var cbASucess = null;
function ajaxSelect(url, sync, data, func){
	cbCnt++;
	defaults = {};
	var params = $.extend(defaults, data);
	ajaxProc(
			url,
			sync,
			params,
		function(res){
			cbCnt--;
			try {
				func(res);
			} catch (e) {
				alert("ajaxProc:" + e);
			}

			if(cbSucess!=null) cbSucess(data,res);
			if(cbCnt==0) {
				if(cbASucess!=null) cbASucess();
			}
		}
	);
}

function ajaxProc(url, sync, data, doSuccess){
	try {
		Modal.showLoading();
	    $.ajax({
	        type: "POST",
	        dataType:"json",
	        url: url,
	        contentType: 'application/x-www-form-urlencoded; charset=utf-8',
	        data: data,
			async: sync,
			cache: false,
	        success: function (response) {
	                //var rec = eval('(' + response + ');');
	                var rec = response;
	        	try {
	                if(rec.status == "ok") {
	                	doSuccess(rec,response);
	                } else {
	                	alert("err:" + rec.message);
	                }
	            } catch (e) {
	                alert("err2:"+e+"||res:"+response);
	            }
	            Modal.hideLoading();
	        },
	        failure: function (response) {
	        	alert("err1 : " + response);
	        	Modal.hideLoading();
	        }
	    });
	} catch (e) {
		alert(e);
	}
}


function execDaumPostcode(onrPost, onrAddr1, onrAddr2) {
    new daum.Postcode({
        oncomplete: function(data) {
            var fullAddr = ''; //
            var extraAddr = ''; //

            if (data.userSelectedType === 'R') { //
                fullAddr = data.roadAddress;

            } else {
                fullAddr = data.jibunAddress;
            }

            if(data.userSelectedType === 'R'){
                if(data.bname !== ''){
                    extraAddr += data.bname;
                }
                if(data.buildingName !== ''){
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                fullAddr += (extraAddr !== '' ? ' ('+ extraAddr +')' : '');
            }

            $("#"+onrPost).val(data.zonecode);
            $("#"+onrAddr1).val(fullAddr);
            $("#"+onrAddr2).focus();
        }
    }).open();
}

(function($) {
    $.fn.jqueryPager = function(options) {
        var defaults = {
            pageSize: 10,
            currentPage: 1,
            pageTotal: 0,
            pageBlock: 10,
            clickEvent: 'callList'
        };

        var subOption = $.extend(true, defaults, options);

        return this.each(function() {
            var currentPage = subOption.currentPage*1;
            var pageSize = subOption.pageSize*1;
            var pageBlock = subOption.pageBlock*1;
            var pageTotal = subOption.pageTotal*1;
            var clickEvent = subOption.clickEvent;

            if (!pageSize) pageSize = 10;
            if (!pageBlock) pageBlock = 10;

            var pageTotalCnt = Math.ceil(pageTotal/pageSize);
            var pageBlockCnt = Math.ceil(currentPage/pageBlock);
            var sPage, ePage;
            var html = '';

            if (pageBlockCnt > 1) {
                sPage = (pageBlockCnt-1)*pageBlock+1;
            } else {
                sPage = 1;
            }

            if ((pageBlockCnt*pageBlock) >= pageTotalCnt) {
                ePage = pageTotalCnt;
            } else {
                ePage = pageBlockCnt*pageBlock;
            }

            html += '<ul>';

            if (sPage > 1) {
                //html += '<a href="javascript:'+ clickEvent +'(1);" class="prev"><img src="/images/pub/btn_prev.gif" alt="이전" /></a>';
                html += '<li><a href="javascript:'+ clickEvent +'(' + (sPage-pageBlock) + ');" class="prev">prev</a></li>';
            }

            for (var i=sPage; i<=ePage; i++) {

	            html += '<li>';
                if (currentPage == i) {
                    html+= '<strong>' + i + '</strong>';
                } else {
                    html+= '<a href="javascript:'+ clickEvent +'(' + i + ');">' + i + '</a>';
                }
	            html += '</li>';
            }


            if (ePage < pageTotalCnt) {
               html+= '<li><a href="javascript:'+ clickEvent +'(' + (ePage+1) + ');" class="next">next</a></li>';
               //html += '<a href="javascript:'+ clickEvent +'(' + pageTotalCnt + ');" class="next"><img src="/images/pub/btn_next.gif" alt="다음" /></a>';
            }
            html += '<ul>';

            $(this).empty().html(html);
      });
    };
})(jQuery);

/**
 *
 * @param  {[type]} str [description]
 * @param  {[type]} m   [description]
 * @return {[type]}     [description]
 */
function pad(str, m) {
	str += "";
	return str.length < m ? pad("0" + str, m) : str;
}

/**
 *
 * @param  {[type]} v [description]
 * @return {[type]}   [description]
 */
function strToint(v) {
	if((!v)||v=="") return 0;
	v = String(v).replace(/,/g, '');
	return parseInt(v, 10);
}

/**
 *  자바스크립트의 replaceAll 기능 구현
 */
function replaceAll(str, orgStr, repStr){
	return str.split(orgStr).join(repStr);
}

/**
 * 콤마찍기
 */
function commaNumber(str) {
    str = String(str);
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}


/**
 * 콤마풀기
 */
function uncommaNumber(str) {
    str = String(str);
    return str.replace(/[^\d]+/g, '');
}

/**
 * 값 입력시 콤마찍기
 */
function inputNumberFormat(obj) {
    obj.value = commaNumber(uncommaNumber(obj.value));
}


/**
 * 쿠키생성
 * @param cName
 * @param cValue
 * @param cDay
 */
function setCookie(cName, cValue, cDay){
	$.cookie(cName, cValue, { expires: cDay, path: '/'});
}

/**
 * 쿠키가져오기
 * @param cName
 * @returns
 */
function getCookie(cName) {
	return $.cookie(cName);
}

/**
 * 쿠키삭제
 * @param cName
 */
function removeCookie(cName){
	$.cookie(cName, null);
}

/**
 * 전체쿠키삭제
 */
function removeAllCookie(){
	var cookies = $.cookie();
    for(var cookie in cookies) {
       $.removeCookie(cookie);
    }
}
