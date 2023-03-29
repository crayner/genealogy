'use strict';

import React, { Component } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';

class EditParentCategories extends Component {
    constructor(props) {
        super(props);
        this.removeCategory = this.removeCategory.bind(this);
        this.path = props.path;
        this.title = props.title;
    }

    removeCategory() {
        window.location.href = this.path;
    }

    render() {
        return (
            <span style={{color: '#003300'}} title={this.title} onClick={this.removeCategory}>
                <FontAwesomeIcon icon={solid('delete-left')}/>
            </span>
        );
    }
}

export default EditParentCategories;
