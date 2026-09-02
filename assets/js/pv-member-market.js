jQuery(function($){
  if(!window.pvMemberMarket) return;
  function run(type,currency,amount){
    return $.post(pvMemberMarket.ajaxurl,{action:'pv_member_action',type:type,doviz:currency,miktar:amount||'',security:pvMemberMarket.nonce});
  }
  window.listemeEkle=function(doviz){run('insert_liste',doviz).done(function(r){if(r&&r.success)location.reload();else alert('Bir hata oluştu.');}).fail(function(){alert('Bir hata oluştu.');});};
  window.listedenCikar=function(doviz){run('delete_liste',doviz).done(function(r){if(r&&r.success)location.reload();else alert('Bir hata oluştu.');}).fail(function(){alert('Bir hata oluştu.');});};
  window.alarmCikar=function(doviz){run('delete_alarm',doviz).done(function(r){if(r&&r.success)location.reload();else alert('Bir hata oluştu.');}).fail(function(){alert('Bir hata oluştu.');});};
  window.alarmKur=function(doviz){var miktar=window.prompt('Haberdar olmak istediğiniz miktarı girin');if(miktar===null)return;run('insert_alarm',doviz,miktar).done(function(r){if(r&&r.success)location.reload();else alert('Bir hata oluştu.');}).fail(function(){alert('Bir hata oluştu.');});};
});
