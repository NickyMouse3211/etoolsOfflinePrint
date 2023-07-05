<hr class="my-4 dashed">
					<div class="form-group row">
						<label class="col-md-3 control-label"><?php echo lang('toko_atau_koleksi', '', '', array('UC'=>'first')); ?>
							<span class="required">*</span>
						</label>
						<div class="col-md-8">
							<select id="store_code" name="store_code" class="global-select2v2 form-control" link="globalfetch" t="<?php echo sencode('cc_store')?>" s="<?php echo sencode('ccstore_code,ccstore_name')?>" required placeholder="<?php echo lang('toko_atau_koleksi', '', '', array('UC'=>'first')); ?>" style="width: 100%">
							   
							</select>
							<div class="form-control-focus"> </div>
							<!-- <span class="help-block">From Parent</span> -->
						</div>
					</div>
					<hr class="my-4 dashed branch_code-display" style="display: none;">
					<div class="form-group row branch_code-display" style="display: none;">
						<?php 
								// nama inputan di $formname , akan otomatis membuat beberapa atribut , yaitu 
								// 1. nama , placeholder dan help block sesuai variable $formname
								// 2. membuat ID dan nama inputan dengan semua huruf kecil dan setiap space di replace underscore
								$formname    = 'branch_code'; 
								$required    = true;
								$select2     = true; //true > select 2 dengan source link , false > select 2 biasa
								$select2link = base_url('fetch/get_branch_code_by_store'); //link untuk select 2 dengan source link
								$value       = json_encode(array('value' => '', 'text' => ''));// pengisian value default select 2
								$option      = array(array('value' => 'I', 'text' => 'Internal'),array('value' => 'E', 'text' => 'External'));// opton jika select2 nya false
								$class 	     = 'branch_code';
								$lowercase   = strtolower(str_replace(' ', '_', $formname));
							?>
						<label class="col-md-3 control-label"><?php echo ucfirst(lang('cabang_toko_atau_koleksi', '', '', array('UC'=>''))); //kalau mau ganti label nya bisa replace php ini ?>
							<span class="required"><?php echo $required == true ? '*' : ''; ?></span>
						</label>
						<div class="col-md-8">
							<select
								id          ="<?php echo $lowercase; ?>" 
								class       ="<?php echo $select2 == true ? 'global-select2v2' : 'select2'; ?> <?php echo $class; ?> form-control"
								link 		="<?php echo $select2link != ''? $select2link : '#'; ?>" 
								name        ="<?php echo $lowercase;?>"
								placeholder ="<?php echo ucfirst(lang('cabang_toko_atau_koleksi', '', '', array('UC'=>''))); ?>"
								<?php echo $required == true ? 'required' : ''; ?> 
								data-id-store_code = "store_code"
							>
								<?php
									$selectvalue = json_decode($value);
									if ($select2 == true) {
										if ($selectvalue->value != '') {
											?>
												<option value="<?php echo $selectvalue->value; ?>" selected><?php echo $selectvalue->text; ?></option>
											<?php
										}
									}else{
										foreach ($option as $key => $value) {
											?>
												<option value="<?php echo $value['value']; ?>" <?php echo $value['value'] == $selectvalue->value ? 'selected' : '' ?>><?php echo $value['text']; ?></option>
											<?php
										}
									}
								?>
							</select>
						</div>
					</div>


$('#store_code').on('select2:select',  function (e) {
		var data = e.params.data;
		$('.branch_code-display').show();
		$( "#card_size_code" ).val(null).trigger('change');
	});
