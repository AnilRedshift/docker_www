jQuery(document).ready(function ($) {
  /* MASONARY GRID */
  // https://github.com/haltu/muuri/issues/416
  Muuri.defaultPacker.destroy();
  Muuri.defaultPacker = new Muuri.Packer(0);
  var grid = new Muuri('.grid');
});
