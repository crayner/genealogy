'use strict';

import React, { Component } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';

class EditParentCategories extends Component {
    constructor(props) {
        super(props);
        this.sayHello = this.sayHello.bind(this);
        this.id = props.id;
        this.title = props.title;
    }

    sayHello() {
        var location = document.getElementById('category_location_form');
        var parents = document.getElementById('parent_category_form');
        location.classList.toggle('collapsed')
        parents.classList.toggle('collapsed')
    }

    render() {
        return (
            <span id={this.id} style={{color: '#003300'}} onClick={this.sayHello} title={this.title}>
                <FontAwesomeIcon icon={solid('pencil')}/>
            </span>
        );
    }
}

export default EditParentCategories;
