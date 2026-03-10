(function ($) {
  const monthNames = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
  const week = ['ش','ی','د','س','چ','پ','ج'];

  function toPersianDigits(input){
    return String(input).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
  }

  function buildCalendar($root){
    const $grid = $root.find('.wpcp-grid');
    const events = JSON.parse($grid.attr('data-events') || '[]');
    const occasions = JSON.parse($grid.attr('data-occasions') || '[]');
    const combined = [...events, ...occasions];

    let now = new Date();
    let year = now.getFullYear();
    let month = now.getMonth();

    const $weekdays = $root.find('.wpcp-weekdays').empty();
    week.forEach(w => $weekdays.append(`<span>${w}</span>`));

    const $monthSelect = $root.find('.wpcp-month');
    const $yearSelect = $root.find('.wpcp-year');
    monthNames.forEach((m,i)=>$monthSelect.append(`<option value="${i}">${m}</option>`));
    for(let y = year-2; y<=year+5; y++) $yearSelect.append(`<option value="${y}">${toPersianDigits(y)}</option>`);

    function render(){
      $monthSelect.val(String(month));
      $yearSelect.val(String(year));
      $grid.empty();
      const first = new Date(year, month, 1);
      const start = (first.getDay()+1)%7;
      const days = new Date(year, month+1, 0).getDate();

      for(let i=0;i<start;i++) $grid.append('<div class="wpcp-day empty"></div>');

      for(let day=1; day<=days; day++){
        const iso = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        const dayEvents = combined.filter(e=>e.date===iso);
        const isToday = day===now.getDate() && month===now.getMonth() && year===now.getFullYear();
        const dots = dayEvents.length ? '<span class="wpcp-dot"></span>' : '';
        const labels = dayEvents.map(e=>`<li>${e.title}</li>`).join('');
        $grid.append(`<div class="wpcp-day ${isToday?'today':''}"><strong>${toPersianDigits(day)}</strong>${dots}<ul>${labels}</ul></div>`);
      }

      const gd = new Date(year, month, now.getDate()).toLocaleDateString('en-CA');
      $root.find('.wpcp-gregorian-date').text(gd);
    }

    $root.find('.wpcp-prev').on('click', ()=>{month--; if(month<0){month=11;year--;} render();});
    $root.find('.wpcp-next').on('click', ()=>{month++; if(month>11){month=0;year++;} render();});
    $monthSelect.on('change', function(){month = parseInt($(this).val(),10); render();});
    $yearSelect.on('change', function(){year = parseInt($(this).val(),10); render();});
    $root.find('.wpcp-today-btn').on('click', ()=>{now = new Date(); year=now.getFullYear(); month=now.getMonth(); render();});

    render();
    loadPrayerTimes($root);
  }

  function loadPrayerTimes($root){
    const city = $root.find('.wpcp-pray-times').data('city');
    $.getJSON(`${wpcpData.ajaxUrl}?action=wpcp_pray_times&city=${encodeURIComponent(city)}`)
      .done((res)=>{
        if(!res.success){ throw new Error('fail'); }
        const t = res.data;
        $root.find('.wpcp-pray-times').html(`فجر: ${t.Fajr} | طلوع: ${t.Sunrise} | ظهر: ${t.Dhuhr} | مغرب: ${t.Maghrib}`);
      })
      .fail(()=>{
        $root.find('.wpcp-pray-times').text('خطا در دریافت اوقات شرعی');
      });
  }

  $(function(){ $('.wpcp-calendar').each(function(){ buildCalendar($(this)); }); });
})(jQuery);
