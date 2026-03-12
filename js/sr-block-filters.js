const { addFilter } = wp.hooks;

// Add allowed blocks to List Item element
const sr_addAllowedBlocksToListItem = (settings) => {
    if (settings.name === 'core/list-item') {
        // Ensure allowedBlocks exists
        if (!settings.allowedBlocks) {
            settings.allowedBlocks = [];
        }
        
        // Add the galley/image blocks to the allowed nested blocks
        settings.allowedBlocks.push('core/gallery');
        settings.allowedBlocks.push('core/image');
    }
    return settings;
};

addFilter('blocks.registerBlockType', 'sr-add-gallery-to-list-item', sr_addAllowedBlocksToListItem);
