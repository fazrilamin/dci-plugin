
jQuery(document).ready(function($) {
    let nodeIndex = $("#nodes-wrapper .node-block").length;
    let connIndex = $("#connections-wrapper .conn-block").length;

    // Add Node button
    $('#add-node').on('click', function() {
        $('#nodes-wrapper').append(`<div class="node-block">
            Name: <input type="text" name="dci_nodes[${nodeIndex}][name]" />
            Location: <input type="text" name="dci_nodes[${nodeIndex}][location]" />
            Bandwidth: <input type="text" name="dci_nodes[${nodeIndex}][bandwidth]" />
            Top: <input type="number" name="dci_nodes[${nodeIndex}][top]" />
            Left: <input type="number" name="dci_nodes[${nodeIndex}][left]" />
            Size: <input type="number" name="dci_nodes[${nodeIndex}][size]" value="30" />
            <button type="button" class="remove-node">Remove</button>
        </div>`);
        nodeIndex++;
    });

    // Add Connection button
    $('#add-connection').on('click', function() {
        $('#connections-wrapper').append(`<div class="conn-block">
            From: <select name="dci_connections[${connIndex}][from]" class="node-select"></select>
            To: <select name="dci_connections[${connIndex}][to]" class="node-select"></select>
            Load:
            <select name="dci_connections[${connIndex}][load]">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <button type="button" class="remove-connection">Remove</button>
        </div>`);
        connIndex++;
        refreshDropdowns();
    });

    // Refresh dropdowns dynamically
    function refreshDropdowns() {
        $('.node-select').each(function() {
            const selected = $(this).val();
            const options = $('#nodes-wrapper .node-block').map(function(idx) {
                const name = $(this).find('input[name*="[name]"]').val() || "Node " + idx;
                return `<option value="${idx}">${name}</option>`;
            }).get().join('');
            $(this).html(options).val(selected);
        });
    }

    // Remove handlers
    $(document).on('click', '.remove-node', function() { $(this).parent().remove(); });
    $(document).on('click', '.remove-connection', function() { $(this).parent().remove(); });

    // Live Admin Preview Renderer
    $('form').on('input change', function() {
        refreshDropdowns();
        const nodes = [];
        $('#nodes-wrapper .node-block').each(function() {
            const node = {
                name: $(this).find('input[name*="[name]"]').val(),
                bandwidth: $(this).find('input[name*="[bandwidth]"]').val(),
                top: parseInt($(this).find('input[name*="[top]"]').val()) || 0,
                left: parseInt($(this).find('input[name*="[left]"]').val()) || 0,
                size: parseInt($(this).find('input[name*="[size]"]').val()) || 30
            };
            nodes.push(node);
        });

        const connections = [];
        $('#connections-wrapper .conn-block').each(function() {
            connections.push({
                from: parseInt($(this).find('select[name*="[from]"]').val()) || 0,
                to: parseInt($(this).find('select[name*="[to]"]').val()) || 0,
                load: $(this).find('select[name*="[load]"]').val()
            });
        });

        // Render nodes
        const previewNodes = $('#dci-preview-nodes');
        previewNodes.empty();
        nodes.forEach((node, i) => {
            previewNodes.append(`<div class="pulse" style="width:${node.size}px; height:${node.size}px; top:${node.top}px; left:${node.left}px; line-height:${node.size}px;">${node.name}</div>`);
        });

        // Render lines with dropdown values!
        const svg = $('#dci-preview-lines').empty();
        connections.forEach(conn => {
            if (nodes[conn.from] && nodes[conn.to]) {
                const from = nodes[conn.from];
                const to = nodes[conn.to];
                const x1 = from.left + from.size/2;
                const y1 = from.top + from.size/2;
                const x2 = to.left + to.size/2;
                const y2 = to.top + to.size/2;

                let stroke = 'green';
                if (conn.load === 'medium') stroke = 'orange';
                if (conn.load === 'high') stroke = 'red';

                svg.append(`<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${stroke}" stroke-width="2" />`);
            }
        });
    });

    $('form').trigger('input');
});
