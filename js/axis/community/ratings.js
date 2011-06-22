/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 */

(function($){
    $.fn.ratings = function(){
        var container = $(this).parent();
        $(this).each(function(i, el){
            var content = '<a href="javascript:void(0)" id="rating-' +
                this.id + '" class="review-stars review-rate0" title="' +
                this.id + '"><span>0</span></a><input type="hidden" name="' +
                this.name + '" class="required"/>';

            $(this).remove();
            $(container.get(i)).prepend(content);
        });

        $('a.review-stars', container).each(function(){
            $(this).before($('label[for="' + this.id.replace('rating-', '') + '"]', container));
            new rating(this);
        })
    }

    var rating = function(el)
    {
        var params = {
            el: el,
            value: '0.0',
            offest: {},
            size: {}
        }
        var rating = 0;
        $(el).hover(initialize, resetStars);
        $(el).bind('mousemove', updateStars);
        $(el).bind('click', setRating);
        $('body').bind('resetForm', reset);

        function initialize(e)
        {
            params.offest = $(this).offset(); // call here in case of draggable window
            params.size.width = $(this).width();
            params.size.height = $(this).height();
        }

        function updateStars(e)
        {
            if (typeof params.offest.left != 'number') {
                params.offest = $(this).offset(); // call here in case of draggable window
                params.size.width = $(this).width();
                params.size.height = $(this).height();
            }

            var value = (5*(e.pageX - params.offest.left)/params.size.width).toPrecision(2);
            var number = 0.5;
            rating = getRating(value, number);
            var newClass = 'review-rate' +
            (rating.indexOf('.5') != -1 ? rating.replace('.5', 'h') : rating.replace('.0', ''));

            var oldClass = $(this).attr('class').replace('review-stars ', '');
            $(this).removeClass(oldClass);
            $(this).addClass(newClass);
        }

        function resetStars()
        {
            var newClass = 'review-rate' +
            (params.value.indexOf('.5') != -1 ? params.value.replace('.5', 'h') : params.value.replace('.0', ''));

            var oldClass = $(params.el).attr('class').replace('review-stars ', '');
            $(params.el).removeClass(oldClass);
            $(params.el).addClass(newClass);
        }

        function setRating()
        {
            params.value = rating.toString();

            $(this).nextAll().filter('input').attr('value', params.value);

            return false;
        }

        function reset()
        {
            params.value = '0.0';
            resetStars();
        }
    }

    var getRating = function(value, number)
    {
        if (typeof value != 'number') {
            value = parseFloat(value);
        }

        var remainder = value % number;
        if (remainder > 0) {
            value = value - remainder + number;
        }

        return value.toFixed(1).toString();
    }

})(jQuery);