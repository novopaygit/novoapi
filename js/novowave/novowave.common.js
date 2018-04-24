
// *********************************************************************************
Date.prototype.format = function(f) {
    if (!this.valueOf()) return " ";
 
    var weekName = ["일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일"];
    var d = this;
     
    return f.replace(/(yyyy|yy|MM|dd|E|hh|mm|ss|a\/p)/gi, function($1) {
        switch ($1) {
            case "yyyy": return d.getFullYear();
            case "yy": return (d.getFullYear() % 1000).zf(2);
            case "MM": return (d.getMonth() + 1).zf(2);
            case "dd": return d.getDate().zf(2);
            case "E": return weekName[d.getDay()];
            case "HH": return d.getHours().zf(2);
            case "hh": return ((h = d.getHours() % 12) ? h : 12).zf(2);
            case "mm": return d.getMinutes().zf(2);
            case "ss": return d.getSeconds().zf(2);
            case "a/p": return d.getHours() < 12 ? "오전" : "오후";
            default: return $1;
        }
    });
};

String.prototype.string = function(len){var s = '', i = 0; while (i++ < len) { s += this; } return s;};
String.prototype.zf = function(len){return "0".string(len - this.length) + this;};
Number.prototype.zf = function(len){return this.toString().zf(len);};
// *********************************************************************************
var Common = {
	isIE : function() {
		var agent = navigator.userAgent.toLowerCase();
		if (!(navigator.appName == 'Netscape' && navigator.userAgent.search('Trident') != -1) && !(agent.indexOf("msie") != -1) ) return false;
		return rue;
	},
	importJS : function(js_file) {
		$('script:last').after('<script type="text/javascript" src="'+ js_file +'"></script>');
	},
	clearSelectBox : function($selector, is_all) {
		if (typeof(is_all) == 'undefined') is_all = true;
		var $first = null;
		if (!is_all) $first = $('option:first', $selector);
		$selector.empty();
		if ($first != null) $selector.append($first);
	},
	addSelectBox : function($selector, json) {
		for (var k in json) {
			$selector.append('<option value="'+ k +'">'+ json[k] +'</option>');
		}
	},
	replaceSelectBox : function($selector, json, is_all) {
		this.clearSelectBox($selector, is_all);
		this.addSelectBox($selector, json);
	},
	getDiffMonth : function(diff) {
		if (typeof diff == 'undefined') diff = 0;
		var today = new Date(globalNowDateTime.substring(0, 10));
		if (diff != 0) today.setMonth(today.getMonth() + diff);
		
		var yy = today.getFullYear().toString();
		var mm = (today.getMonth() + 1).toString();

		var new_ym =  yy +'-'+ (mm[1] ? mm : '0'+mm[0]);
		return new_ym;
	},
	getDiffDate : function(diff) {
		if (typeof diff == 'undefined') diff = 0;
		var today = new Date(globalNowDateTime.substring(0, 10));
		switch (diff) {
			case 'first' :
				today = new Date(globalNowDateTime.substring(0, 8) +'01');
				break;
			case 'last' :
				var yy = globalNowDateTime.substring(0, 4);
				var mm = parseInt(globalNowDateTime.substring(5, 7), 10);
				today = new Date(yy, mm, 0);
				break;
			default :
				if (diff != 0) today.setDate(today.getDate() + diff);
		}
		
		var yy = today.getFullYear().toString();
		var mm = (today.getMonth() + 1).toString();
		var dd = today.getDate().toString();

		var new_date =  yy +'-'+ (mm[1] ? mm : '0'+mm[0]) +'-'+ (dd[1] ? dd : '0'+dd[0]);
		return new_date;
	},
	isUndefined : function(el) {
		if (typeof(el) == 'undefined') return true;
		return false;
	}
};
// *********************************************************************************
var Debug = {
	showAddDebug : function(text) {
		this.addDebug(text);
		this.showDebug();
	},
	addDebug : function(text) {
		$('#wrap-debug .debug-text').append($('<div><pre>'+ text +'</pre></div>'));
	},
	showDebug : function() {
		$('#wrap-debug').show();
	},
	hdieDebug : function() {
		$('#wrap-debug').hide();
	}
};
// *********************************************************************************
var Modal = {
	showLoading : function() {
		jQuery('.wrap-loading').show();
	},
	hideLoading : function() {
		jQuery('.wrap-loading').hide();
	},
	closeBox4Form : function($frm) {
		jQuery('.close', $frm.closest('.modal-box')).trigger('click');
	},
	showLayerPopup : function(modal_id, container) {
		var $modal;
		if (typeof(container) == 'undefined') {
			$modal = jQuery('#'+ modal_id);
		} else {
			$modal = jQuery('#'+ modal_id, container);
		}
		if ($modal.length < 1) return false;

		var parent_modal_id = '';

		$modal.fadeIn();
		jQuery("body").append('<div class="modal-overlay modal-overlay_'+ modal_id +'"></div>');
		jQuery(".modal-overlay").fadeTo(100, 0.6);

		//블라인드 팝업갯수 구하기 (숫자 0부터)
		var modalBoxCnt  = jQuery('[class=modal-box]').length - 1;
		for (i=0; i <= modalBoxCnt; i++) {
			var modalArrayId = $("[class=modal-box]:eq("+ i +")").attr("id");
			if (modal_id != modalArrayId) {
				var $now_modal = jQuery('#'+ modalArrayId);
				if ($now_modal.is(':visible') && $now_modal.css('z-index') == '1000') {
					parent_modal_id = modalArrayId;
				}
				$now_modal.css("z-index", "1");
				$modal.css("z-index","1000");
			}
		}

		jQuery(window).resize(function() {
			var win_h = jQuery(window).height();
			var win_w = jQuery(window).width();
			var box_h = $modal.height() + 5;

			if (win_h > box_h) {
				css_top  = (win_h - $modal.outerHeight()) / 2;
				css_left = (win_w - $modal.outerWidth()) / 2;
			} else {
				$modal.height(win_h - 10);
				jQuery('.modal-body', $modal).height(win_h - 110);
				css_top  = 10;
				css_left = (win_w - $modal.outerWidth()) / 2;
			}
			var css = {};
			css['top'] = css_top;
			css['left'] = css_left;
			css['position'] = 'fixed';
			//css['margin'] = '0 auto';
			$modal.css(css);
		});

		jQuery(window).resize();

		jQuery('.close', $modal).click(function() {
			_closeModal();
			return false;
		});
		jQuery("."+ modal_id +"_close").click(function() {
			_closeModal();
			return false;
		});
		function _closeModal() {
			$modal.fadeOut(1, function() {
				if (parent_modal_id) {
					jQuery("#"+ parent_modal_id).css("z-index", "1000");
					jQuery(".modal-overlay_"+modal_id).remove();
				} else {
					for (k = modalBoxCnt; k >= 0; k--) {
						var modalArrayId = jQuery("[class=modal-box]:eq("+ k +")").attr("id");
						if (modalArrayId == modal_id) {
							var openModal = jQuery("[class=modal-box]:eq("+ parseInt(k - 1) +")").attr("id");
							jQuery("#"+ openModal).css("z-index", "1000");
							jQuery(".modal-overlay_"+modal_id).remove();
							break;
						}
					}
				}
			});
		}
	},
	hideLayerPopup : function(modal_id) {
		jQuery('.close', jQuery('#'+ modal_id)).trigger('click');
		jQuery("."+ modal_id +"_close").trigger('click');
	},
	bindLayerPopupOne : function($element) {
		$element.bind('click', function(e) {
			var $btn = $(this);
			if ($btn.hasClass('btnGs1') || $btn.hasClass('btnSb1') || $btn.hasClass('btnLb1')) return false;
			e.preventDefault();

			var modal_id = $btn.attr('data-modal-id');
			Modal.showLayerPopup(modal_id);
		});
	},
	unbindLayerPopupOne : function($element) {
		$element.unbind('click');
	},
	bindLayerPopup : function() {
		jQuery('[data-modal-id]').each(function() {
			Modal.bindLayerPopupOne(jQuery(this));
		});
	},
};
// *********************************************************************************
var DatePicker = {
	changeYearButtons : function($el) {
		setTimeout(function() {
			var widgetHeader = $el.datepicker("widget").find(".ui-datepicker-header");
			var prevMonthBtn = $el.datepicker('widget').find('.ui-datepicker-prev');
			var nextMonthBtn = $el.datepicker('widget').find('.ui-datepicker-next');
			var prevYrBtn = $('<a href="" class="ui-datepicker-prev-year"></a>');
			prevYrBtn.append($('<span class="ui-icon ui-icon-circle-arrow-w">이전해</span>'));
			prevYrBtn.unbind("click").bind("click", function() {
				$.datepicker._adjustDate($el, -1, 'Y');
				return false;
			}).unbind('mouseover').bind('mouseover', function() {
				$(this).addClass('ui-state-hover ui-datepicker-prev-year-hover');
			}).unbind('mouseout').bind('mouseout', function() {
				$(this).removeClass('ui-state-hover ui-datepicker-prev-year-hover');
			});
			var nextYrBtn = $('<a href="" class="ui-datepicker-next-year"></a>');
			nextYrBtn.append($('<span class="ui-icon ui-icon-circle-arrow-e">다음해</span>')).unbind("click").bind("click", function() {
				$.datepicker._adjustDate($el, +1, 'Y');
				return false;
			}).unbind('mouseover').bind('mouseover', function() {
				$(this).addClass('ui-state-hover ui-datepicker-next-year-hover');
			}).unbind('mouseout').bind('mouseout', function() {
				$(this).removeClass('ui-state-hover ui-datepicker-next-year-hover');
			});
			prevMonthBtn.before(prevYrBtn);
			nextMonthBtn.after(nextYrBtn);
			//prevYrBtn.insertBefore(prevMonthBtn);
			//prevYrBtn.appendTo(widgetHeader);
			//nextYrBtn.appendTo(widgetHeader);
		}, 1);
	},
	initElement : function($selectors, max) {
		var gDate = max ? '1900-01-01' : '2010-01-01';
		var changeYearButtons = function() {
		};
		$selectors.each(function() {
			var before_date = '';
			$(this).datepicker({
				showOn: "button",
				dateFormat: 'yy-mm-dd', 
				changeMonth: true, 
				changeYear: true ,
				showAnim: "slide",
				showButtonPanel: true,
				//buttonImage: "images/calendar.png",
				buttonImage: "/js/images/icon_calendar.gif",
				buttonImageOnly: true,
				yearRange: 'c-99:c+5',
				minDate: gDate,
				//yearSuffix: '년',
				showMonthAfterYear: true,
				monthNames      : ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
				monthNamesShort : ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
				nextText : '다음달',
				prevText : '이전달',
				//stepMonths: 12,
				dayNames      : ['일', '월', '화', '수', '목', '금', '토'],
				dayNamesShort : ['일', '월', '화', '수', '목', '금', '토'],
				dayNamesMin   : ['일', '월', '화', '수', '목', '금', '토'],
				currentText: '오늘',
				closeText : '닫기',
				beforeShow: function(input) {
					//console.log('beforeShow');
					//console.log(input);
					DatePicker.changeYearButtons($(input));
					return;
					setTimeout(function() {
						var widgetHeader = $(input).datepicker("widget").find(".ui-datepicker-header");
						var prevYrBtn = $('<button title="PrevYr">&lt;&lt; Prev Year</button>');
						prevYrBtn.unbind("click").bind("click", function() {
							$.datepicker._adjustDate($(input), -1, 'Y');
						});
						var nextYrBtn = $('<button title="NextYr">Next year &gt;&gt;</button>');
						nextYrBtn.unbind("click").bind("click", function() {
							$.datepicker._adjustDate($(input), +1, 'Y');
						});
						prevYrBtn.appendTo(widgetHeader);
						nextYrBtn.appendTo(widgetHeader);
					}, 1);
				},
				onChangeMonthYear: function(input) {
					DatePicker.changeYearButtons($(this));
					return;
					setTimeout(function() {
						var widgetHeader = $(input).datepicker("widget").find(".ui-datepicker-header");
						var prevYrBtn = $('<button title="PrevYr">&lt;&lt; Prev Year</button>');
						prevYrBtn.unbind("click").bind("click", function() {
							$.datepicker._adjustDate($(input), -1, 'Y');
						});
						var nextYrBtn = $('<button title="NextYr">Next year &gt;&gt;</button>');
						nextYrBtn.unbind("click").bind("click", function() {
							$.datepicker._adjustDate($(input), +1, 'Y');
						});
						prevYrBtn.appendTo(widgetHeader);
						nextYrBtn.appendTo(widgetHeader);
					}, 1);
				},
			}).blur(function() {
				var date;
				if ($(this).val().trim() == '') {
					date = '';
				} else {
					date = Utils.formatterDate($(this).val());
					if (date == '') date = before_date;
				}
				$(this).val(date);
			}).focus(function(event) {
				before_date = $(this).val();
			});
			if ($(this).hasClass('disable')) $(this).datepicker( "option", "disabled", true );
		});
	},
}
// *********************************************************************************
var winCustomerSalesInfo;
var BizCommon = {
	isDisableButton : function($btn) {
		if ($btn.hasClass('btnG1') || $btn.hasClass('btnSG1') || $btn.hasClass('btnGs1') || $btn.hasClass('btnSb1') || $btn.hasClass('btnLb1')) return true;
		return false;
	},
	setDateYM : function($selector) {
		$selector.each(function() {
			var before_ym = '';
			$(this).focus(function(event) {
				before_ym = $(this).val();
			}).blur(function() {
				var ym;
				if ($(this).val().trim() == '') {
					ym = '';
				} else {
					ym = Utils.formatterYearMonth($(this).val());
					if (ym == '') ym = before_ym;
				}
				$(this).val(ym);
			});
		});
	},
	setOnlyNumber : function($selector) {
		// 8 : tab, 9 : backspace, 37 : ←, 39 : →, 35 : END, 36 : HOME
		$selector.keypress(function(event) {
			var enables = [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 37, 39, 8, 9, 35, 36];
			var key_code = event.which;
			if ($.inArray(key_code, enables) < 0) {
				event.preventDefault();
			}
		}).keydown(function(event) {
			var key_code = event.which;
			if (key_code == 229) {  // 한글
				event.preventDefault();
				return false;
			} else if ((event.ctrlKey == true && $.inArray(key_code, [118, 86]) > -1) || (event.shiftKey==true && key_code == 45)) { // 붙여넣기 제외 : ctrl + F7(118), ctrl + V(86), shift + INSERT(45)
				event.preventDefault();
				return false;
			}
		}).keyup(function(event) {
			//$(this).val(Utils.addComma($(this).val()));
		}).focus(function() {
			$(this).val( $(this).val().replace(/,/gi,''));
		}).blur(function() {
			$(this).val( Utils.addComma($(this).val()));
		}).contextmenu(function(event) {
			event.preventDefault();
		}).css('ime-mode','disabled');
	},
	popPostSearch : function(zipno, addr1, addr2, addr3, etc) {
		BizCommon.popPostSearchGubun('', zipno, addr1, addr2, addr3, etc)
	},
	popPostSearchGubun : function(gubun, zipno, addr1, addr2, addr3, etc) {
		if (gubun == '') {
			gubun = 'road';
		} else {
			gubun = $(':radio[name="'+ gubun +'"]:checked').val() == '1' ? 'old' : 'road';
		}
		if (typeof(etc) == 'undefined') etc = '';
		var url = '/web/popup/post_search';
		url += '?gubun='+ gubun;
		url += '&zipno='+ zipno;
		url += '&addr1='+ addr1;
		url += '&addr2='+ addr2;
		url += '&addr3='+ addr3;
		url += '&etc='+ etc;
		url += '&return_url='+ document.location.href;
		var popPostSearch = window.open(url,'popPostSearch','width=570,height=420, scrollbars=yes, resizable=yes');
	},
	downloadFile : function(type, key_value) {
		var down_url = '/common/download.php?type='+ type +'&key_value='+ key_value;
		document.location.href = down_url;
	},
	downloadExcelTitleBase : function() {
		return $('.title-2nd').text();
	},
	downloadExcelTitleTab : function(tab_code) {
		var tit = this.downloadExcelTitleBase();
		$('.wrap-tabs.subtab_menus').each(function(idx) {
			var $tab = $(this);
			if ($tab.attr('tabcode') != tab_code) return true;
			var tab_title = $('.tabs-title', $('#wrap-tabs-bar > .tabs-header')).eq(idx).text();
			if (tab_title) tit += '_'+ tab_title;
			return false;
		});
		return tit;
	},
	downloadMultiExcel : function(lists) {
		var tab_code = objLayout.getSelectedTabCode();
		var $excel_list = $('#gpop_multi_excel_download_list');
		$excel_list.empty();
		for (var i=0, max=lists.length; i<max; i++) {
			var row = lists[i];
			//{'grid_nm':'gridList_LT', 'act_url':objLayout.tabs[tab_code].action_url, 'file_nm':tit+'_기사별작업현황_요약'}
				var $tr = $('<tr></tr>');
				$tr.append('<td><label style="cursor: pointer;"><input type="radio" name="idx" value="'+ i +'" /> '+ row.file_nm.replace(/_/g, ' ') +'</label></td>');
				$excel_list.append($tr);
		}
		$('#btnMultiExcelDownload').unbind('click').click(function() {
			var f = document.frmGlobalMultiExcel;
			var idx = -1;
			for (var i=0, max=f.idx.length; i<max; i++) {
				if (f.idx[i].checked) {
					idx = f.idx[i].value;
					break;
				}
			}
			if (idx < 0) {
				alert('다운로드할 영역을 선택하시기 바랍니다.');
				return false;
			}
			var info = lists[i];
			BizCommon.downloadExcel(info['grid_nm'], info['act_url'], info['file_nm']);
			BizCommon.hideModalLayerPopup('gpop_multi_excel_download');
			return false;
		});
		BizCommon.showModalLayerPopup('gpop_multi_excel_download');
		var obj = objLayout.tabs[tab_code];
	},
	downloadExcel : function(grid_nm, act_url, file_nm, tab_code) {
		if (typeof(file_nm) == 'undefined') file_nm = this.downloadExcelTitleBase();
		//if (typeof(tab_code) == 'undefined') tab_code = 'body';
		var tab_code = objLayout.getSelectedTabCode();
		var obj = objLayout.tabs[tab_code];
		if (typeof(obj.area[grid_nm]) == 'undefined' || !obj.area[grid_nm]) {
			alert('다운받을 엑셀 영역을 확인 바랍니다.');
			return;
		}
		if (act_url == '') {
			alert('다운받을 엑셀 경로가 필요합니다.');
			return;
		}

		var $grid = obj.area[grid_nm];
		if ($grid.getGridParam('records') < 1) {
			alert('다운받을 자료가 존재하지 않습니다.');
			return;
		}
		// col model
		var colModel = $grid.jqGrid('getGridParam', 'colModel');
		var grid_params = {
			'label':[], 'name':[], 'width':[], 'align':[], 'formatter':[]
		};
		for (var i=0, max=colModel.length; i<max; i++) {
			var col = colModel[i];
			if (col['hidden'] || col['name'] == 'rownum' || col['name'] == 'cb' || col['formatter'] == 'checkbox' || col['name'] == 'custcode_salesinfo') continue;
			if (typeof(col['formatter']) == 'function') {
				var fn_str = col['formatter'].toString();
				if (fn_str.indexOf('fmt_phone') > -1) {
					col['formatter'] = 'fmt_phone';
				} else {
					col['formatter'] = undefined;
				}
			};
			for (var k in grid_params) {
				grid_params[k].push(col[k]);
			}
		}
		// group header
		grid_params['grp_tit'] = [];
		grid_params['grp_col'] = [];
		var group_header = $grid.jqGrid('getGridParam', 'groupHeader');
		if (group_header) {
			for (var i=0, max=group_header[0].groupHeaders.length; i<max; i++) {
				var col = group_header[0].groupHeaders[i];
				grid_params['grp_tit'].push(col['titleText']);
				grid_params['grp_col'].push(col['numberOfColumns']);
			}
		}
		// footer
		grid_params['footer'] = [];
		var footer = $grid.jqGrid('footerData', 'get');
		for (var k in footer) {
			var val = footer[k];
			if (Valid.checkNumber(val)) grid_params['footer'].push(k);
		}

		// make query string
		var grid_data = obj.getGridParamData(grid_nm);;
		if (grid_data) {
			delete grid_data['page'];
			delete grid_data['pagesize'];
			delete grid_data['excelmake'];
			delete grid_data['excel_make'];
		} else {
			grid_data = {};
		}

		var params = [];
		params.push('excelmake=Y');
		for (var k in grid_data) {
			params.push(k +'='+ grid_data[k]);
		}
		params.push('excelFile=' + file_nm);
		params.push('grp_tit=' + grid_params['grp_tit'].join('|'));   // 그룹헤더명칭
		params.push('grp_col=' + grid_params['grp_col'].join('|'));   // 그룹헤더명칭
		params.push('title=' + grid_params['label'].join('|'));     // 셀명칭
		params.push('field=' + grid_params['name'].join('|'));      // 필드명
		params.push('width=' + grid_params['width'].join('|'));     // 셀크기
		params.push('align=' + grid_params['align'].join('|'));     // 셀정렬
		params.push('format='+ grid_params['formatter'].join('|')); // 셀타입
		params.push('footer='+ grid_params['footer'].join('|'));    // 셉합계타입
		var dataString = params.join('&');
		//console.log(act_url +'?'+ dataString);
		process.location.href = act_url + '?' + dataString;
	},
};

// ********************************************************************************* 
var ProcessPopup = {
	initRender : function(el_id, title_nm, cols_list, is_result) {
		var html = [];
		html.push('<div id="modal_process_'+ el_id +'" class="modal-box">');
		html.push('	<!-- --------------------------------------- header -->');
		html.push('	<div class="modal_header">');
		html.push('		<a href="#" class="close">×</a>');
		html.push('		<h3 id="modal_process_'+ el_id +'_title">'+ title_nm +'</h3>');
		html.push('	</div>');
		html.push('	<!-- --------------------------------------- body -->');
		html.push('	<div class="modal-body" style="width:650px; height:400px; overflow:auto;">');
		html.push('		<div style="width: 96%;">');
		html.push('			<table border="0" cellspacing="0" cellpadding="0" width="100%" height="30">');
		html.push('				<tr>');
		html.push('					<td width="50">진행율</td>');
		html.push('					<td><hr id="process_'+ el_id +'_rate" style="background-color: steelblue; border: none; height: 10px; width: 100%;" /></td>');
		html.push('					<td width="100" class="tcenter"><span id="process_'+ el_id +'_res">0</span> / <span id="process_'+ el_id +'_tot">0</span></td>');
		html.push('				</tr>');
		if (is_result) {
			html.push('				<tr>');
			html.push('					<td colspan="3">');
			html.push('						성공 : <span id="process_'+ el_id +'_success">0</span>');
			html.push('						<span style="margin: 0 5px;">/</span>');
			html.push('						실패 : <span id="process_'+ el_id +'_fail">0</span>');
			html.push('					</td>');
			html.push('				</tr>');
		}
		html.push('			</table>');
		html.push('		</div>');
		html.push('		<div style="width: 96%; height: 350px; overflow-y: scroll; border: solid 1px #cccccc; padding: 5px;">');
		html.push('			<table border="0" cellspacing="0" cellpadding="0" width="100%" class="gray_list">');
		html.push('				<thead>');
		html.push('					<tr>');
		if (typeof(cols_list) == 'object') {
			var len = cols_list.length;
			if (!Common.isUndefined(len)) {
				for (var i=0; i<len; i++) {
					var row = cols_list[i];
					var col_width = Common.isUndefined(row['width']) ? '' : row['width'];
					var col_title = Common.isUndefined(row['title']) ? '' : row['title'];
					var tr = '<th class="tcenter"';
					if (col_width) tr += ' width="'+ col_width +'"';
					tr += '>'+ col_title +'</th>';
					html.push('						'+ tr);
				}
			}
		}
		html.push('					</tr>');
		html.push('				</thead>');
		html.push('				<tbody id="modal_process_'+ el_id +'_result">');
		html.push('				</tbody>');
		html.push('			</table>');
		html.push('		</div>');
		html.push('	</div>');
		html.push('	<!-- --------------------------------------- footer -->');
		html.push('	<div class="modal_footer">');
		html.push('		<a href="#" class="btn btn-small close">닫 기</a>');
		html.push('	</div>');
		html.push('</div>');
		return html.join('');
	},
	initShow : function(el_id, cnt_request, title_nm, callback) {
		$('#process_'+ el_id +'_rate').width(0);
		$('#process_'+ el_id +'_tot').text(Utils.addComma(cnt_request));
		$('#process_'+ el_id +'_res').text(Utils.addComma(0));
		this.getProcessResult(el_id).empty();
		if (title_nm != '' && !Common.isUndefined(title_nm)) $('#modal_process_'+ el_id +'_title').text(title_nm);
		BizCommon.showModalLayerPopup('modal_process_'+ el_id);

		if (typeof callback == 'function') {
			var timer = setTimeout(function() {
				if (ProcessPopup.isShowing(el_id)) {
					clearTimeout(timer);
					callback();
				}
			}, 500);
		}
	},
	isShowing : function(el_id) {
		var $el = $('#modal_process_'+ el_id);
		if ($el.length < 1) return false;
		if ($el.is(':visible')) return true;
		return false;
	},
	showRate : function(el_id, cnt_request, cnt_response, callback) {
		var rate = parseInt(cnt_response * 100 / cnt_request, 10);
		$('#process_'+ el_id +'_rate').width(rate +'%');
		$('#process_'+ el_id +'_res').text(Utils.addComma(cnt_response));
		if (cnt_request == cnt_response) {
			if ($('tr', this.getProcessResult(el_id)).length == 0) {
				//BizCommon.hideModalLayerPopup('modal_process_'+ el_id);
			}
			if (typeof callback == 'function') {
				callback();
			}
		}
	},
	showRateResult : function(el_id, cnt_request, cnt_response, cnt_success, cnt_fail, callback) {
		//console.log(typeof(cnt_success));
		if (typeof(cnt_success) == 'undefined' || cnt_success == null) cnt_success = 0;
		if (typeof(cnt_fail) == 'undefined' || cnt_fail == null) cnt_fail = 0;
		var rate = parseInt(cnt_response * 100 / cnt_request, 10);
		$('#process_'+ el_id +'_rate').width(rate +'%');
		$('#process_'+ el_id +'_res').text(Utils.addComma(cnt_response));
		$('#process_'+ el_id +'_success').text(Utils.addComma(cnt_success));
		$('#process_'+ el_id +'_fail').text(Utils.addComma(cnt_fail));
		if (cnt_request == cnt_response) {
			if ($('tr', this.getProcessResult(el_id)).length == 0) {
				//BizCommon.hideModalLayerPopup('modal_process_'+ el_id);
			}
			if (typeof callback == 'function') {
				callback();
			}
		}
	},
	hide : function(el_id) {
		BizCommon.hideModalLayerPopup('modal_process_'+ el_id);
	},
	getProcessResult : function(el_id) {
		return $('#modal_process_'+ el_id +'_result');
	},
	writeError : function(el_id, html) {
		var $tr = typeof(html) == 'string' ? $(html) : html;
		this.getProcessResult(el_id).append($tr);
	},
	writeResult : function(el_id, html) {
		var $tr = typeof(html) == 'string' ? $(html) : html;
		this.getProcessResult(el_id).append($tr);
	},
	getPopup : function(el_id) {
	}
}
// ********************************************************************************* jQuery Extend
jQuery.fn.extend({
	onlyEngNum : function() {       // 영문,숫자만 입력가능
		this.keypress(function(event) {
			//alert('keypress');
			if(!(event.which>=48 && event.which<=57) && !(event.which>=65 && event.which<=90) && !(event.which>=97 && event.which<=122) && event.which!=0 && event.which!=8) {
				event.preventDefault();
			}
		}).keydown(function(event) {
			//alert('keydown');
			if ((event.ctrlKey==true && (event.which == '118' || event.which == '86')) || (event.shiftKey==true && event.which == '45')) {
				event.preventDefault();
			}
		}).contextmenu(function(event) {
			//alert('contextmenu');
			event.preventDefault();
		});
	},
	onlyNum : function() {  //숫자만 입력가능
		this.css("ime-mode","disabled");
		this.keypress(function(event) {
			if(!(event.which>=48 && event.which<=57) && event.which!=0 && event.which!=8) {
				event.preventDefault();
			}
		}).keydown(function(event) {
			if ((event.ctrlKey==true && (event.which == '118' || event.which == '86')) || (event.shiftKey==true && event.which == '45')) {
				event.preventDefault();
			} 
		}).contextmenu(function(event) {
			event.preventDefault();
		});
	},
	
});
// *********************************************************************************
$(document).ready(function() {
	Modal.bindLayerPopup();
	/*$(document).click(function(event) {
		if (BizCommon.isDisableButton($(this))) {
			event.preventDefault();
			return false;
		}
	});*/
});
//Common.importJS('/js/bizrental.grid.js');
