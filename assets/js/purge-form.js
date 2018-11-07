jQuery(document).ready(function($) {

  $('#purge').submit(function() {

    var data = {
      action: 'register_purge',
      value: 'purge now'
    }

    jQuery.post(ajaxurl, data, function () {
      alert('Nginx FastCGI Cache Purge All Initiated');
    })

    return false;
  });

});