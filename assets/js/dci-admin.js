
jQuery(document).ready(function($) {
    let nodeIndex = $("#nodes-wrapper .node-block").length;
    let connIndex = $("#connections-wrapper .conn-block").length;

    $('#add-node').on('click', function() {
        $('#nodes-wrapper').append(`<div class="node-block">
            Name: <input type="text" name="dci_nodes[${nodeIndex}][name]" />
            Location: <input type="text" name="dci_nodes[${nodeIndex}][location]" />
            Bandwidth: <input type="text" name="dci_nodes[${nodeIndex}][bandwidth]" />
            Top: <input type="number" name="dci_nodes[${nodeIndex}][top]" />
            Left: <input type="number" name="dci_nodes[${nodeIndex}][left]" />
            <button type="button" class="remove-node">Remove</button>
        </div>`);
        nodeIndex++;
    });

    $('#add-connection').on('click', function() {
        $('#connections-wrapper').append(`<div class="conn-block">
            From: <input type="number" name="dci_connections[${connIndex}][from]" />
            To: <input type="number" name="dci_connections[${connIndex}][to]" />
            Load:
            <select name="dci_connections[${connIndex}][load]">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <button type="button" class="remove-connection">Remove</button>
        </div>`);
        connIndex++;
    });

    $(document).on('click', '.remove-node', function() {
        $(this).parent().remove();
    });
    $(document).on('click', '.remove-connection', function() {
        $(this).parent().remove();
    });
});
