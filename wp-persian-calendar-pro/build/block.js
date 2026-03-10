(function(blocks, element){
  const el = element.createElement;
  blocks.registerBlockType('wpcp/calendar', {
    title: 'Persian Calendar Pro',
    icon: 'calendar-alt',
    category: 'widgets',
    edit: function(){ return el('p', {}, 'Persian Calendar Pro block.'); },
    save: function(){ return null; }
  });
})(window.wp.blocks, window.wp.element);
