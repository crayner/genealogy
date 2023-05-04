'use strict';
import React, {Component} from "react"
import PropTypes from 'prop-types'
import IndividualDetails from "./IndividualDisplay";

export default class IndividualApp extends Component {
    constructor(props) {
        super(props);

        this.translations = props.translations;
        this.search = props.search
        this.state = {
            individual: props.individual,
            form: props.form,
        };

        this.handleOpenForm = this.handleOpenForm.bind(this);
    }

    handleOpenForm(sectionName) {
        let sections = this.state.sections;
        let section = sections[sectionName];
        Object.keys(sections).map(name => {
            sections[name] = false;
        })
        sections[sectionName] = true ^ section;
        let form = this.state.form;
        if (sectionName === 'webpages') {
            let webpages;
            let prototype;
            let index;
            form.children.map((child,i) => {
                if (child.id === 'category_webpages') {
                    webpages = child;
                    prototype = {...child.prototype}
                    index = i;
                }
            });
            prototype.full_name = prototype.full_name.replace('__name__', 'prototype');
            prototype.name = prototype.name.replace('__name__', 'prototype');
            prototype.id = prototype.id.replace('__name__', 'prototype');
            prototype.children.map((child,i) => {
                child = {...child};
                child.full_name = child.full_name.replace('__name__', 'prototype');
                child.id = child.id.replace('__name__', 'prototype');
                prototype.children[i] = {...child}
            })
            webpages.children.push(prototype);
            form[index] = {...prototype};
        }
        this.setState({
            form: form,
            template: this.state.template,
            sections: sections,
        })
    }

    render() {
        return (<IndividualDetails translations={this.translations}
                                   individual={this.state.individual}
                                   handleOpenForm={this.handleOpenForm}
                                   search={this.search}
        />);
    }
}

IndividualApp.propTypes = {
    individual: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
}
