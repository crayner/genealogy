'use strict';
import React, { Component } from "react"
import PropTypes from 'prop-types'
import IndividualList from "./IndividualList";
import SidebarManager from "./SidebarManager";
import SetFormElementValue from "../form/SetFormElementValue";
import { setFormElement, getFormElementById, followUrl, buildFormData } from "../form/FormManager";
import {fetchJson} from '../Component/fetchJson'
import {initialiseSections} from "./SectionsManager";
import OpenFormSection from "./OpenFormSection";
import styled from "styled-components";
import { Sidebar, Main, MainContainer, H3, Border, FlexboxContainer } from '../component/StyledCSS';

export const DarkGreenP = styled.p`
    color: #003300;
`

export default class CategoryApp extends Component {
    constructor(props) {
        super(props);

        this.state  = {
            category: props.category,
            form: props.form,
            sections: initialiseSections(props.form.template),
        };
        this.translations = props.translations;
        this.form = props.form

        this.renderParents = this.renderParents.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.handleSave = this.handleSave.bind(this);
        this.handleOpenForm = this.handleOpenForm.bind(this);
        this.handleClose = this.handleClose.bind(this);
        this.handleFormChange = this.handleFormChange.bind(this);

        this.functions = {
            renderParents: this.renderParents,
            handleChange: this.handleChange,
            handleSave: this.handleSave,
            handleOpenForm: this.handleOpenForm,
            handleClose: this.handleClose,
            handleFormChange: this.handleFormChange,
        }
    }

    renderParents(parents) {
        var result = parents.map((parent, i, array) => {
            if (array.length - 1 === i) {
                return (<span key={i}><a href={parent.path}>{parent.name}</a></span>)
            }
            return (<span key={i}><a href={parent.path}>{parent.name}</a> | </span>)
        })
        return (<DarkGreenP>{this.translations.Categories}: {result} <OpenFormSection sectionName={'parents'} translations={this.translations} handleOpenForm={this.handleOpenForm} /></DarkGreenP>)
    }

    handleChange(event, form) {
        console.log(event, form)
        const { value } = event.target;
        event.value = value;
        this.elementChange(event, form.id, form.type)
    }

    handleFormChange(event, element, value) {
        if (typeof value === 'object') {
            value = value.value;
        } else {
            value = event.target.innerText
        }
        if ('state' in element && element.state.userInput !== value) value = element.state.userInput;

        event.target.value = value;
        this.form = setFormElement(element, this.form);
        this.elementChange(event, element.id, element.type)
    }

    elementChange(event, id, type) {
        if (id !== 'ignore_me') {
            let element = getFormElementById(this.form, id, true);
            element = SetFormElementValue(event, element, type);

            if (typeof element.attr.onChange !== 'undefined') {
                followUrl(element.attr.onChange, element);
            }
            setFormElement(element, this.form)
            this.setState({
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
            form: this.state.form,
            template: this.state.template,
            sections: sections,
        })
    }

    handleClose(sectionName) {
        let sections = this.state.sections;
        sections[sectionName] = false;
        this.setState({
            form: this.state.form,
            template: this.state.template,
            sections: sections,
        })
    }

    handleSave(section) {
        this.data = buildFormData({}, this.form);
        fetchJson(
            this.form.template[section].action,
            {method: this.form.method, body: JSON.stringify(this.data)},
            false)
            .then(data => {
                this.elementList = {}
                this.form = data.form
                if (!(!data.template || /^\s*$/.test(data.template)))
                    this.template = data.template
                this.setState({
                    form: this.form,
                    template: this.template,
                })
            }).catch(error => {
            console.error('Error: ', error)
            this.setState({
                form: this.form,
                template: this.template,
            })
        })
    }

    render() {
        return (
            <FlexboxContainer>
                <Border></Border>
                <Main>
                    <MainContainer>
                    <H3>{this.translations['Category']}: { this.state.category.name } <OpenFormSection sectionName={'name'} translations={this.translations} handleOpenForm={this.handleOpenForm} /></H3>
                        {this.renderParents(this.state.category.parents)}
                    </MainContainer>
                    <IndividualList
                        translations={this.translations}
                        individuals={this.state.category.individuals}
                        {...this.functions}
                    />
                </Main>
                <Sidebar>
                    <SidebarManager
                        category={this.state.category}
                        translations={this.translations}
                        form={this.state.form}
                        functions={this.functions}
                        sections={this.state.sections}
                    />
                </Sidebar>
                <Border />
            </FlexboxContainer>
        );
    }
}

CategoryApp.propTypes = {
    category: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired
}
