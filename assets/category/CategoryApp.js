'use strict';
import React, { Component } from "react"
import PropTypes from 'prop-types'
import IndividualList from "./IndividualList";
import SidebarManager from "./SidebarManager";
import SetFormElementValue from "../form/SetFormElementValue";
import {setFormElement, getFormElementById, followUrl, buildFormData, isOKtoSave} from "../form/FormManager";
import {setMessageByName, cancelMessageByName} from "../form/MessageManager";
import {fetchJson} from '../Component/fetchJson'
import {initialiseSections} from "./SectionsManager";
import OpenFormSection from "./OpenFormSection";


export default class CategoryApp extends Component {
    constructor(props) {
        super(props);
        this.messages = []

        this.state  = {
            category: props.category,
            form: props.form,
            messages: this.messages,
            sections: initialiseSections(props.form.template),
        };
        this.translations = props.translations;
        this.form = props.form

        this.renderParents = this.renderParents.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.handleSave = this.handleSave.bind(this);
        this.handleOpenForm = this.handleOpenForm.bind(this);
        this.handleClose = this.handleClose.bind(this);
    }

    renderParents(parents) {
        var result = parents.map((parent, i, array) => {
            if (array.length - 1 === i) {
                return (<span key={i}><a href={parent.path}>{parent.name}</a></span>)
            }
            return (<span key={i}><a href={parent.path}>{parent.name}</a> | </span>)
        })
        return (<p>{this.translations.Categories}: {result}</p>)
    }

    handleChange(event, form) {
        event.preventDefault();
        const { value } = event.target;
        this.elementChange(event, form.id, form.type)
    }

    elementChange(event, id, type){
        if (id !== 'ignore_me') {
            let element = getFormElementById(this.form, id);
            element = SetFormElementValue(event, element, type);

            if (typeof element.attr.onChange !== 'undefined') {
                followUrl(element.attr.onChange, element);
            }

            if (element.errors.length > 0)
                element.errors.map(error => {
                    setMessageByName(element.id, (element.label ? element.label : element.name) + + ' (' + (!(!element.value || /^\s*$/.test(element.value)) ? JSON.stringify(element.value) : '{empty}') + '): ' + error)
                })
            else
                cancelMessageByName(element.id)

            setFormElement(element, this.form)
            this.setState({
                messages: this.messages,
                form: this.form,
                template: this.template,
                sections: this.state.sections
            })
        }
    }

    handleOpenForm(sectionName) {
        let sections = this.state.sections;
        sections[sectionName] = true;
        this.setState({
            messages: this.state.messages,
            form: this.state.form,
            template: this.state.template,
            sections: sections,
        })
    }

    handleClose(sectionName) {
        let sections = this.state.sections;
        sections[sectionName] = false;
        this.setState({
            messages: this.state.messages,
            form: this.state.form,
            template: this.state.template,
            sections: sections,
        })
    }

    handleSave(section) {
        this.data = buildFormData({}, this.form)
        console.log(this.form)
        if (isOKtoSave(this.messages)) {
            this.messages = []
            fetchJson(
                this.form.template[section].action,
                {method: this.form.method, body: JSON.stringify(this.data)},
                false)
                .then(data => {
                    this.elementList = {}
                    this.messages = this.messages.concat(data.messages)
                    this.form = data.form
                    if (!(!data.template || /^\s*$/.test(data.template)))
                        this.template = data.template
                    this.setState({
                        form: this.form,
                        messages: this.messages,
                        template: this.template,
                    })
                }).catch(error => {
                console.error('Error: ', error)
                this.messages.push({level: 'danger', message: error})
                this.setState({
                    form: this.form,
                    messages: this.messages,
                    template: this.template,
                })
            })
        } else {
            const message = {level: 'dark', message: translateMessage(this.translations, 'All errors must be cleared before the form can be saved!')}
            this.messages.push(message)
            this.setState({
                form: this.form,
                messages: this.messages,
                template: this.template,
            })
        }
    }

    render() {
        return (
            <div className="flexbox-container">
                <div className="border"></div>
                <div className="main">
                    <div className="main-container">
                    <h3>{this.translations['Category']}: { this.state.category.name } <OpenFormSection sectionName={'name'} translations={this.translations} handleOpenForm={this.handleOpenForm} /></h3>
                        {this.renderParents(this.state.category.parents)}
                    </div>
                    <IndividualList
                        translations={this.translations}
                        individuals={this.state.category.individuals}
                    />
                </div>
                <div className="sidebar">
                    <SidebarManager
                        category={this.state.category}
                        translations={this.translations}
                        form={this.state.form}
                        handleChange={this.handleChange}
                        handleSave={this.handleSave}
                        handleClose={this.handleClose}
                        sections={this.state.sections}
                    />
                </div>
                <div className="border"></div>
            </div>
        );
    }
}

CategoryApp.propTypes = {
    category: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired
}
