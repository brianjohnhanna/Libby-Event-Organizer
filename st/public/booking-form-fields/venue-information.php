<div id="venueOptions" style="display:none;">
  <div id="venueDescription"></div>

  <div id="venueSetup">
    <h3>Setup Option*</h3>
    <div></div>
  </div>

  <div id="venueEquipment">
    <h3>Available Equipment Options</h3>
    <div></div>
  </div>
</div>

<script type="text/html" id="tmpl-setup-option">
  <label>
     <input type="radio" name="libby[_libby_setup]" value="{{data.title}}" />
      {{{data.title}}}<# if (data.description) { #> - {{{data.description}}}<# } #>
      <# if (data.diagram) { #>(<a href="{{data.diagram}}" target="_blank">View Diagram</a>)<# } #>
  </label>
</script>

<script type="text/html" id="tmpl-equipment">
  <label>
     <input type="checkbox" name="libby[_libby_equipment][]" value="{{{data.title}}}" />
      {{{data.title}}}<# if (data.description) { #> - {{{data.description}}}<# } #><# if (data.training_required) { #> <strong>(Training Required)</strong><# } #>
      <# if (data.image) { #>(<a href="{{data.image}}" target="_blank">View Image</a>)<# } #>
  </label>
</script>
