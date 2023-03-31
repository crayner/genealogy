'use strict';
import React, {Component, Fragment} from "react"
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
import RenderCategoryParents from "./RenderCategoryParents";

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
            messages: [],
        };
        this.translations = props.translations;
        this.form = props.form

        this.handleChange = this.handleChange.bind(this);
        this.handleSave = this.handleSave.bind(this);
        this.handleOpenForm = this.handleOpenForm.bind(this);
        this.handleClose = this.handleClose.bind(this);
        this.handleFormChange = this.handleFormChange.bind(this);
        this.removeParentCategory = this.removeParentCategory.bind(this);
        this.fetchChoices = this.fetchChoices.bind(this);
        this.clearMessage = this.clearMessage.bind(this);

        this.functions = {
            handleChange: this.handleChange,
            handleSave: this.handleSave,
            handleOpenForm: this.handleOpenForm,
            handleClose: this.handleClose,
            handleFormChange: this.handleFormChange,
            removeParentCategory: this.removeParentCategory,
            fetchChoices: this.fetchChoices,
            clearMessage: this.clearMessage,
        }
    }

    handleChange(event, form) {
        console.log(event, form)
        const { value } = event.target;
        event.value = value;
        this.elementChange(event, form.id, form.type)
    }

    handleFormChange(event, element, value) {

        if (element.type === 'collection') {
            if (value.value !== '') {
                if (typeof element.value !== 'object') element.value = [];
                element.value.push(value);
                element.data = element.value;
                this.form = setFormElement(element, this.form);
                let category = {...this.state.category}
                if (element.name === 'parents') {
                    Object.keys(element.value).map(i => {
                        const item = element.value[i];
                        let ok = false;
                        Object.keys(category['parents']).map(o => {
                            let parent = category['parents'][o];
                            if (parent.id === Number(item.value)) {
                                ok = true;
                            }
                        })
                        if (!ok) {
                            let parent = {
                                id: Number(item.value),
                                name: item.label,
                                path: "/genealogy/category/" + Number(item.value) + "/modify",
                            };
                            category['parents'].push(parent);
                        }
                     })
                }
                this.setState({
                    form: {...this.form},
                    category: {...category},
                });
                return;
            } else {
                this.form = setFormElement(element, this.form);
                this.setState({
                    form: this.form,
                });
                return;
            }
        }

        if (element.type === 'choice') {
            element.value = value.value;
            element.data = element.value;
            this.form = setFormElement(element, this.form);
            this.setState({
                form: this.form,
            });
            return;
        }

        if (typeof value === 'object') {
            value = value.value;
        } else {
            value = event.target.innerText
        }
        if ('state' in element && element.state.userInput !== value) value = element.state.userInput;

        event.target.value = value;
        this.form = setFormElement(element, this.form);
        this.elementChange(event, element.id, element.type === 'collection' ? 'choice' : element.type)
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
                this.form = data.form;
                let messages = {...this.state.messages};
                if (typeof data.message === 'object')  {
                    const x = messages.length + 1;
                    messages[x] = data.message;
                    if (messages[x].timeOut > 0) {
                        this.messageTimeout(messages[x]);
                    }
                }
                let sections = {...this.state.sections};
                sections[section] = false;
                this.setState({
                    form: this.form,
                    messages: messages,
                    sections: sections,
                });
            }).catch(error => {
            console.error('Error: ', error)
            this.setState({
                form: this.form,
            })
        })
    }

    messageTimeout(message) {
        const tick = () => this.clearMessage(message.id);
        const timer = window.setTimeout(tick, message.timeOut);
        return () => window.clearTimeout(timer);
    }

    removeParentCategory(section, parent){
        const template = this.form.template[section];
        const value = this.form.value.id;
        let remove = template['remove'];
        remove = remove.replace('{category}', value);
        remove = remove.replace('{parent}', parent);
        fetchJson(
            remove,
            {method: 'POST', body: JSON.stringify(this.data)},false)
            .then(data => {
                this.elementList = {};
                this.form = data.form;
                this.setState({
                    form: this.form,
                    category: data.category
                })
            }).catch(error => {
            console.error('Error: ', error);
            this.setState({
                form: this.form,
            })
        })
    }

    fetchChoices(suggestions, form, section, search) {
        if (search.length < 3) return suggestions;
        if (typeof this.form.template[section].fetch[form.name] === 'string') {
            fetchJson(
                this.form.template[section].fetch[form.name],
                {method: 'POST', body: JSON.stringify({search: search})},false)
                .then(data => {
                    form.choices = data.choices
                    form.state.filteredSuggestions = data.choices
                    this.form = setFormElement(form, this.form);

                    this.setState({
                        form: this.form,
                    })

                }).catch(error => {
                console.error('Error: ', error);
                return suggestions;
            })
        }

        return suggestions;
    }

    clearMessage(id) {
        const result = Object.keys(this.state.messages).filter(index => {
            const message = this.state.messages[index]
            if (message.id !== id) return message;
        });
        this.setState({
            messages: result
        });
    }

    render() {
        return (
            <FlexboxContainer>
                <Border />
                <Main>
                    <MainContainer>
                    <H3>{this.translations['Category']}: { this.state.category.name } <OpenFormSection sectionName={'name'} translations={this.translations} handleOpenForm={this.handleOpenForm} /></H3>
                        <RenderCategoryParents translations={this.translations}
                                               parents={this.state.category.parents}
                                               handleOpenForm={this.handleOpenForm} />
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
                        messages={this.state.messages}
                        clearMessage={this.clearMessage}
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
