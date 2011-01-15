$(".overlay_target").each(function(i, el) {
  $(el).overlay({
    target:"#modal",
    mask: {
      color: '#96bfce',
      loadSpeed: 200,
      opacity: 0.9
    },
    top:'10%',
    close:'#modal button.close',
    onBeforeLoad: function() {
      mmh.loadDialog(this.getTrigger().attr("href"));
    },
    onLoad: function() {
      $('#modal button.close').click(function(){ $(el).data("overlay").close(); });
    }
  });
});