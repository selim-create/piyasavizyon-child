jQuery(function($){
  function message($scope,text,error){var $m=$scope.find('.pv-member-message').first();$m.toggleClass('is-error',!!error).text(text).show();}
  $(document).on('submit','.pv-member-ajax-form',function(e){
    e.preventDefault();
    var $form=$(this), type=$form.data('type')||'';
    var data=$form.serializeArray();
    data.push({name:'action',value:'pv_member_action'},{name:'type',value:type},{name:'security',value:pvMember.nonce});
    var $button=$form.find('button[type="submit"]').first(), old=$button.text();
    $button.prop('disabled',true).text('Kaydediliyor...');
    $.post(pvMember.ajaxurl,$.param(data)).done(function(resp){
      if(resp&&resp.success){message($form,(resp.data&&resp.data.message)||'Kaydedildi.',false);}
      else{message($form,(resp&&resp.data&&resp.data.message)||'İşlem tamamlanamadı.',true);}
    }).fail(function(){message($form,'İşlem tamamlanamadı. Lütfen tekrar deneyin.',true);}).always(function(){$button.prop('disabled',false).text(old);});
  });
  $(document).on('click','[data-pv-member-remove]',function(e){
    e.preventDefault();
    var $button=$(this), type=$button.data('pv-member-remove'), currency=$button.data('currency');
    $.post(pvMember.ajaxurl,{action:'pv_member_action',type:type,doviz:currency,security:pvMember.nonce}).done(function(resp){
      if(resp&&resp.success){$button.closest('tr').fadeOut(180,function(){$(this).remove();});}
      else{alert((resp&&resp.data&&resp.data.message)||'İşlem tamamlanamadı.');}
    }).fail(function(){alert('İşlem tamamlanamadı.');});
  });
});
