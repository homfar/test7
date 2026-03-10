(function ($) {
  const persianMonthNames = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
  const weekSat = ['ش','ی','د','س','چ','پ','ج'];
  const weekSun = ['ی','د','س','چ','پ','ج','ش'];

  const pFmt = new Intl.DateTimeFormat('fa-IR-u-ca-persian-nu-latn', {year:'numeric', month:'numeric', day:'numeric'});
  const hFmt = new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura-nu-latn', {year:'numeric', month:'long', day:'numeric'});

  function toFaDigits(input){
    return String(input).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
  }

  function pParts(date){
    const parts = pFmt.formatToParts(date);
    return {
      year: parseInt(parts.find(p=>p.type==='year').value, 10),
      month: parseInt(parts.find(p=>p.type==='month').value, 10),
      day: parseInt(parts.find(p=>p.type==='day').value, 10),
    };
  }

  function startOfPersianMonth(py, pm){
    let d = new Date(py - 622, 1, 15);
    for(let i=0;i<420;i++){
      const pp = pParts(d);
      if(pp.year === py && pp.month === pm && pp.day === 1) return new Date(d);
      d.setDate(d.getDate()+1);
    }
    return new Date();
  }

  function weekNumber(d){
    const dt = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    const dayNum = dt.getUTCDay() || 7;
    dt.setUTCDate(dt.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(dt.getUTCFullYear(),0,1));
    return Math.ceil((((dt - yearStart) / 86400000) + 1)/7);
  }

  function buildCalendar($root){
    const $grid = $root.find('.wpcp-grid');
    const events = JSON.parse($grid.attr('data-events') || '[]');
    const occasions = JSON.parse($grid.attr('data-occasions') || '[]');
    const showWeekNumber = String($grid.data('show-week-number')) === '1';
    const weekStart = String($grid.data('week-start') || 'sat');
    const combined = [...events, ...occasions];

    let today = new Date();
    let current = pParts(today);
    let py = current.year;
    let pm = current.month;

    const $weekdays = $root.find('.wpcp-weekdays').empty();
    (weekStart === 'sun' ? weekSun : weekSat).forEach(w => $weekdays.append(`<span>${w}</span>`));

    const $monthSelect = $root.find('.wpcp-month').empty();
    const $yearSelect = $root.find('.wpcp-year').empty();

    persianMonthNames.forEach((m,i)=>$monthSelect.append(`<option value="${i+1}">${m}</option>`));
    for(let y = py-5; y<=py+5; y++) $yearSelect.append(`<option value="${y}">${toFaDigits(y)}</option>`);

    function render(){
      $monthSelect.val(String(pm));
      $yearSelect.val(String(py));
      $grid.empty();

      let d = startOfPersianMonth(py, pm);
      const firstW = weekStart === 'sun' ? d.getDay() : ((d.getDay()+1)%7);
      for(let i=0;i<firstW;i++) $grid.append('<div class="wpcp-day empty"></div>');

      const monthEvents = [];
      for(let i=0;i<32;i++){
        const pp = pParts(d);
        if(pp.year !== py || pp.month !== pm) break;
        const iso = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        const dayEvents = combined.filter(e=>e.date===iso);
        const isToday = d.toDateString()===today.toDateString();
        const isHoliday = d.getDay()===5;
        const dots = dayEvents.length ? '<span class="wpcp-dot"></span>' : '';
        const labels = dayEvents.map(e=>`<li>${e.title}</li>`).join('');
        $grid.append(`<div class="wpcp-day ${isToday?'today':''} ${isHoliday?'holiday':''}"><strong>${toFaDigits(pp.day)}</strong><small>${toFaDigits(d.getDate())}</small>${dots}<ul>${labels}</ul></div>`);
        monthEvents.push(d.toISOString().slice(0,10));
        d.setDate(d.getDate()+1);
      }

      const gSelected = startOfPersianMonth(py, pm);
      $root.find('.wpcp-gregorian-date').text(`میلادی: ${gSelected.toLocaleDateString('en-CA')}`);
      $root.find('.wpcp-hijri-date').text(`قمری: ${hFmt.format(gSelected)}`);
      $root.find('.wpcp-week-number').text(showWeekNumber ? `هفته: ${toFaDigits(weekNumber(gSelected))}` : '');
    }

    $root.find('.wpcp-prev').on('click', ()=>{pm--; if(pm<1){pm=12;py--;} render();});
    $root.find('.wpcp-next').on('click', ()=>{pm++; if(pm>12){pm=1;py++;} render();});
    $monthSelect.on('change', function(){pm = parseInt($(this).val(),10); render();});
    $yearSelect.on('change', function(){py = parseInt($(this).val(),10); render();});
    $root.find('.wpcp-today-btn').on('click', ()=>{today = new Date(); const p = pParts(today); py=p.year; pm=p.month; render();});

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
