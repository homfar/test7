(function($){
  function setNames(){
    $('#wpcp-events-list .wpcp-event-item').each(function(i){
      $(this).find('input').each(function(){
        const k = $(this).data('name') || ($(this).attr('name')||'').split('[').pop().replace(']','');
        $(this).attr('name', `wpcp_settings[custom_events][${i}][${k}]`);
      });
    });
  }

  $(function(){
    $('.wpcp-tab').on('click', function(){
      const tab = $(this).data('tab');
      $('.wpcp-tab').removeClass('active');
      $(this).addClass('active');
      $('.wpcp-panel').removeClass('active');
      $(`.wpcp-panel[data-panel="${tab}"]`).addClass('active');
    });

    $('#wpcp-events-list').sortable({handle:'.dashicons-menu', update:setNames});

    $('#wpcp-add-event').on('click', function(){
      const html = $($('#wpcp-event-template').html());
      $('#wpcp-events-list').append(html);
      setNames();
    });

    $(document).on('click', '.wpcp-remove-row', function(){
      $(this).closest('.wpcp-event-item').remove();
      setNames();
    });

    setNames();
  });
})(jQuery);
