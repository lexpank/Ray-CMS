<?
 	$new=p('new',1);
	$edit=g('edit',1);
    $act=p('act');
    $id=p('id',1);
    $del=(!empty($_POST['delete'])) ? $_POST['delete'] : 0;
    $redir_str=p('redir_str');
    $required_ok=1;


	$filters=array();
	$url_conf='';


	$order_by=g('order_by');
    $order=g('order');
    $page=g('page',1)!=0 ? g('page',1) : 1;

	$table_headers=array();
	foreach($mod_data['view']['fields'] as $fld_key=>$fld_data){
		}
	}

	$url_conf.="&order_by=$order_by&order=$order";
	$q_sort='ORDER BY ';
	if($order_by!=''){
	}
	else{
	}
	if($order=='DESC' || !empty($mod_data['view']['order_desc']) && $order_by==''){
	}


	$lim = 'LIMIT '.(($page-1)*$mod_data['view']['per_page']).','.$mod_data['view']['per_page'];


	$sql_data=array();
	$images_list=array();
	$files_list=array();
	foreach($mod_data['form']['fields'] as $key=>$fld_info){
		$save_to='';
		if($fld_info['save']=='mysql'){
			$save_to='sql_data';
		}
		if($fld_info['save']=='image'){
		}
		if($fld_info['save']=='file'){
			$files_list[]=$key;
		}
		if($save_to!=''){
			if(!empty($fld_info['langs'])){
	       		foreach($config['langs'] as $lng){
	       			}
					${$save_to}[$key.'_'.$lng]=p($key.'_'.$lng);
	       		}
	       	}
	       	else{
					$required_ok=0;
	       		}
	       		${$save_to}[$key]=p($key);
	       	}
       	}
	}

    if($del!=0){
		$arr=array();
		foreach($del as $id=>$chk){
			if(($chk)&&(is_numeric($id))){
				array_push($arr,$id);
			}
		}
		if(count($arr)>0){
			sql_del($db_table,$arr);
			header("location: ".$data['action'].urldecode($redir_str));
       		die();
		}
	}

	if($act=='add'){
			if(count($images_list)>0){
	       		foreach($images_list as $img){
	       			$res=write_image($img,$mod_data['form']['fields'][$img]['form_fld']['path']);
	       			if(!$res['err']){
	       				$sql_data[$img]=$res['file_name'];
	           			if(!empty($mod_data['form']['fields'][$img]['form_fld']['thumbnails'])){
	           				foreach($mod_data['form']['fields'][$img]['form_fld']['thumbnails'] as $thumb){
	           					if(!empty($thumb['crop'])){
	           						crop_img($mod_data['form']['fields'][$img]['form_fld']['path'],$res['file_name'],$thumb['path'],$thumb['width'],$thumb['height'],$thumb['crop'],1);
	           					}
	           					else{
	                            	write_img_thumb($mod_data['form']['fields'][$img]['form_fld']['path'],$res['file_name'],$thumb['path'],$thumb['width'],$thumb['height']);
	           					}
	           				}
	           			}
	       			}
	       		}
	       	}
			if(count($files_list)>0){
				foreach($files_list as $file){
					$filename=wr_file($file,$mod_data['form']['fields'][$file]['form_fld']['path']);
					if($filename!='error'){
						$sql_data[$file]=$filename;
					}
				}
			}
			if($mod_data['save_time']){
				$sql_data['added_at']='NOW()';
			}
       		sql_insert($db_table,$sql_data);
       	}
       	header("location: ".$data['action'].urldecode($redir_str));
       	die();
	}

	if($act=='edit' && $id!=0){
			if(count($images_list)>0){
				$q=mysql_fetch_array($res);
	       		foreach($images_list as $img){
						if(file_exists($mod_data['form']['fields'][$img]['form_fld']['path'].$q[$img])){
							unlink($mod_data['form']['fields'][$img]['form_fld']['path'].$q[$img]);
						}
						foreach($mod_data['form']['fields'][$img]['form_fld']['thumbnails'] as $thumb){
								unlink($thumb['path'].$q['image']);
							}
						}
						$sql_data[$img]='';
					}


	       			$res=write_image($img,$mod_data['form']['fields'][$img]['form_fld']['path']);
	       			if(!$res['err']){
	       				$sql_data[$img]=$res['file_name'];
	           			if(!empty($mod_data['form']['fields'][$img]['form_fld']['thumbnails'])){
	           					}
	           					else{
	           					}
	           				}
	           			}
	       			}
	       		}
	       	}
			if(count($files_list)>0){
				$res=sql_do("SELECT * FROM $db_table WHERE id='$id'");
				$q=mysql_fetch_array($res);
				foreach($files_list as $file){
					if(p('del_'.$file,1)!=0){
						if(file_exists($mod_data['form']['fields'][$file]['form_fld']['path'].$q[$file])){
							unlink($mod_data['form']['fields'][$file]['form_fld']['path'].$q[$file]);
						}						
						$sql_data[$file]='';
					}
				
					$filename=wr_file($file,$mod_data['form']['fields'][$file]['form_fld']['path']);
					if($filename!='error'){
						$sql_data[$file]=$filename;
					}
				}
			}
			if(isset($mod_data['save_time']) && $mod_data['save_time']){
				$sql_data['updated_at']='NOW()';
			}
	       	sql_update($db_table,$sql_data,$id);
       	}
	}

    //add new element, showing empty form
	if($new!=0){
  		show_form(array(), $mod_data['captions']['add_title'], 'add',$redir_str);
	}

	//edit element, showing filled form for editing
	if($edit!=0){
		$query=preg_replace('/{edit}/',$edit,$query);
		$res=sql_do($query);
		if($q=mysql_fetch_array($res)){
			show_form($q, $mod_data['captions']['edit_title'], 'edit');
		}
	}


    //view list
    if(($new==0)&&($edit==0)){
    	$query=str_replace('SELECT','SELECT SQL_CALC_FOUND_ROWS',$query);
        $query.=" $q_sort $lim";
    	$res=sql_do($query);
    	list($total) = mysql_fetch_row(mysql_query('SELECT FOUND_ROWS()'));
        $pages_count = ceil($total / $mod_data['view']['per_page']);

        if(mysql_num_rows($res)>0){
        	$frm = new InputForm (array('action'=>$data['action'],'mode'=>$data['mod_mode'],'cols'=>count($mod_data['view']['fields'])));
        	$row_data=array();
        	$i=0;
        	//generate header of the table
        	foreach($mod_data['view']['fields'] as $fld_key=>$fld_data){
				if(!empty($fld_data['header']['params'])){
                	}
				}
				$i++;
        	}
        	$frm->addrow($row_data);
        	//generate table data
        	$tmp1='';
        	while($q=mysql_fetch_array($res)){
					$frm->addbreak($tmp1);
        		}
        		$i=0;
                    	$tmp=preg_replace('/{/','\\$q[\'',$tmp);
                    	$tmp=preg_replace('/}/','\']',$tmp);
                    	eval("\$tmp=$tmp");
                    	$row_data[$i]=array('data'=>$tmp);
					}
                	}
                	elseif($fld_key=='actions'){
                	}

                	$i++;
				}
				$frm->addrow($row_data);
        	}
        	$frm->hidden('redir_str',urlencode($url_conf));
        	$frm->show();
        }

        if($mod_data['add_new']){
		    $frm->hidden('new', 1);
		    $frm->hidden('redir_str',urlencode($url_conf));
			$frm->show();
        }
    }

   	function show_form($data, $title, $act,$redir_str=''){
		$fields=$GLOBALS['mod_data']['form']['fields'];
		$config=$GLOBALS['config'];
		$enctype='';
		if(count($GLOBALS['images_list'])>0 || count($GLOBALS['files_list'])>0){
		}

    	$frm = new InputForm (array('action'=>$GLOBALS['data']['action'],'mode'=>$GLOBALS['data']['mod_mode'],'enctype'=>$enctype));

    	$frm->addbreak($title);
	   	if(!empty($data['id'])){
	    	$frm->hidden('id', $data['id']);
	   	}
	   	if($redir_str!=''){
	   	}
	   	$frm->hidden('act', $act);
		$r_edit_fields=array();

	   	foreach($fields as $key=>$fld_info){
	   		if(!empty($fld_info['type']) && $fld_info['type']=='break'){
	   			$frm->addbreak($fld_info['title']);
	   		}
	   		$before=$after='';
	   		if(!empty($fld_info['form_fld']['foreign_id'])){
	   			$before='<div id="'.$fld_info['form_fld']['foreign_id'].'">';
	   			$after='</div>';
	   		}
	   		$extra=!empty($fld_info['form_fld']['extra']) ? $fld_info['form_fld']['extra'] : '';
	   		$size_x=!empty($fld_info['form_fld']['size_x']) ? $fld_info['form_fld']['size_x'] : '';
	   		$size_y=!empty($fld_info['form_fld']['size_y']) ? $fld_info['form_fld']['size_y'] : '';
	   		$sel_data=array();
	   		if(!empty($fld_info['form_fld']['sel_data'])){
	   		}
	   		elseif(!empty($fld_info['form_fld']['sel_query'])){
	 			while($q1=mysql_fetch_array($res1)){
	 				}
	 				else{
	 				}
	 			}
	   		}


	   		if(!empty($fld_info['form_fld'])){
				if(!empty($fld_info['langs'])){
		       		foreach($config['langs'] as $lng){
                        }
		       			switch ($fld_info['form_fld']['function']){
							case 'text_box':
								$frm->simple_form_row($fld_info['title'].' ('.$lng.')',$before.$frm->text_box($key.'_'.$lng,!empty($data[$key.'_'.$lng]) ? $data[$key.'_'.$lng] : '',$size_x,0,false,$extra).$after);
								break;
						    case 'textarea':
						    	$frm->simple_form_row($fld_info['title'].' ('.$lng.')',$before.$frm->textarea($key.'_'.$lng,!empty($data[$key.'_'.$lng]) ? $data[$key.'_'.$lng] : '',$size_x,$size_y,$extra).$after);
						    	break;
						}
		       		}
		       	}
		       	else{
                       	$r_edit_fields[]=$key;
           			}
		       		switch ($fld_info['form_fld']['function']){
						case 'text_box':
							$frm->simple_form_row($fld_info['title'],$before.$frm->text_box($key,!empty($data[$key]) ? $data[$key] : '',$size_x,0,false,$extra).$after);
							break;
						case 'textarea':
							$frm->simple_form_row($fld_info['title'],$before.$frm->textarea($key,!empty($data[$key]) ? $data[$key] : '',$size_x,$size_y,$extra).$after);
						   	break;
						case 'select_tag':
							$frm->simple_form_row($fld_info['title'],$before.$frm->select_tag($key,$sel_data,!empty($data[$key]) ? $data[$key] : '',$extra).$after);
							break;
						case 'select_group_tag':
							$frm->simple_form_row($fld_info['title'],$before.$frm->select_group_tag($key,$sel_data,!empty($data[$key]) ? $data[$key] : '',$extra).$after);
							break;
						case 'file':
							if(!empty($data[$key])){
								$frm->simple_form_row($fld_info['title'],($fld_info['save']=='image' ? '<img src="'.$fld_info['form_fld']['thumbnails'][0]['path'].$data[$key].'" />' : $fld_info['form_fld']['path'].$data[$key]).' &nbsp; '.$frm->checkbox('del_'.$key, '1', $fld_info['del_title']));
								$frm->simple_form_row($fld_info['edit_title'],$frm->file($key));
							}
							else{
							}
							break;
					}
		       	}
		  	}
		}
	   	$frm->show();
	   	if(count($r_edit_fields)!=0){
			?>
				<script type="text/javascript">

			<?
			foreach($r_edit_fields as $fld_name){
				?>CKEDITOR.replace('<?=$fld_name?>');<?
			}?>
			</script>
			<?
		}
	}
?>