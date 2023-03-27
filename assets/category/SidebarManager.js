'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import FormRender from "../form/RenderForm";
import {extractFormSection} from "../form/FormManager";

export default function SidebarManager(props) {
    const {
        category,
        translations,
        form,
        handleChange,
        handleSave,
        handleClose,
        sections
    } = props;

    function splitFormToSections() {
        let result = {};
        Object.keys(form.template).map(i => {
            const child = form.template[i];
            result[child.name] = extractFormSection(form, child.name);
        })
        return result
    }

    function renderSubForms() {
        let subForms = splitFormToSections();
        return Object.keys(subForms).map(i => {
            let child = subForms[i];
            return (<FormRender
                form={child}
                translations={translations}
                handleChange={handleChange}
                handleClose={handleClose}
                handleSave={handleSave}
                identifier={i}
                key={i}
                sections={sections}
            />)
        })
    }

    return (
        <div>
            {renderSubForms()}
            <div className="sidebar-form">
                <div className='form-element'><a href={`https://www.wikitree.com/wiki/Category:${category.name}`}  target="_blank">{category.name} {translations['onWikitree']}</a></div>
            </div>
        </div>
    );
}

SidebarManager.propTypes = {
    category: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    handleSave: PropTypes.func.isRequired,
    handleClose: PropTypes.func.isRequired,
    form: PropTypes.object.isRequired,
    sections: PropTypes.object.isRequired
};