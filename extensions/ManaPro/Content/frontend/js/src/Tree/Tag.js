Mana.define('ManaPro/Content/Tree/Tag', ['jquery', 'Mana/Core/Block', 'singleton:ManaPro/Content/Filter'],
function ($, Block, filter) {
    return Block.extend('ManaPro/Content/Tree/Tag', {
        setFilterUrl: function (link) {
            var tagId = link.data('mTagId');
            if(filter.isTagSelected(tagId)) {
                link.addClass('m-tag-selected');
            }
            link[0].href = !link.hasClass('m-tag-selected') ? filter.getUrlIfTagSelected(tagId) : filter.getUrlIfTagNotSelected(tagId);
        },
        _subscribeToBlockEvents: function () {
            var self = this;

            return this
                ._super()
                .on('load', this, function () {
                    this.$().find('a').each(function(){
                        self.setFilterUrl($(this));
                    });
                })
        }
    });
});