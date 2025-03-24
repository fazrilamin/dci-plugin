
jQuery(document).ready(function($) {
    if (dciData.pulse === 'yes') {
        $('.dci-node').addClass('pulse');
    }
    console.log("DCI Visualizer v1.1.0 loaded with pulse: " + dciData.pulse);
});
