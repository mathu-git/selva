/*
 * Copyright © 2020 CGI. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    CGI <info.de@cgi.com>
 * @copyright 2020 CGI
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define(
    [
    'Magento_Ui/js/form/element/ui-select'
    ],
    function (Select) {
        'use strict';
        return Select.extend(
            {
                setParsed: function (data) {
                    var option = this.parseData(data);
                    if (data.error) {
                        return this;
                    }
                    this.options([]);
                    this.setOption(option);
                    this.set('newOption', option);
                },
                parseData: function (data) {
                    return {
                        value: data.customer.entity_id,
                        label: data.customer.name
                    };
                }
            }
        );
    }
);
