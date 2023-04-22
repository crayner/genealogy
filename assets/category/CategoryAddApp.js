'use strict';
import React, {Component} from "react"
import PropTypes from 'prop-types'
import SidebarManager from "./SidebarManager";
import {Sidebar, Main, MainContainer, H3, Border, FlexboxContainer, Theme} from '../component/StyledCSS';
import {buildFormData, followUrl, getFormElementById, setFormElement} from "../form/FormManager";
import SetFormElementValue from "../form/SetFormElementValue";
import {fetchJson} from "../component/fetchJson";

export default class CategoryAddApp extends Component {
    constructor(props) {
        super(props);

        this.form = props.form;

        this.state  = {
            form: props.form,
            messages: [],
            sections: {
                name: true,
                search: false,
            },
            search: this.extractSearchElement(),
        };
        this.category = {};
        this.translations = props.translations;

        this.handleFormChange = this.handleAddFormChange.bind(this);
        this.removeParentCategory = this.removeAddParentCategory.bind(this);
        this.fetchChoices = this.fetchAddChoices.bind(this);
        this.handleChange = this.handleAddChange.bind(this);
        this.handleSave = this.handleAddSave.bind(this);
        this.handleClose = this.handleAddClose.bind(this);

        this.functions = {
            handleFormChange: this.handleFormChange,
            removeParentCategory: this.removeParentCategory,
            fetchChoices: this.fetchChoices,
            handleChange: this.handleChange,
            handleSave: this.handleSave,
            handleClose: this.handleClose,
        };
    }

    extractSearchElement() {
        let search;
        this.form.children.map((child, i) => {
            if (child.name === 'search') search = child;
        })
        return search;
    }

    handleAddSave(section) {
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
                    category: data.category,
                });
            }).catch(error => {
            console.error('Error: ', error)
            this.setState({
                form: this.form,
            })
        })
    }

    fetchAddChoices(suggestions, form, section, search) {
        if (typeof this.form.template[section].fetch === 'boolean' || search.length < 3) return suggestions;
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

    handleAddClose(sectionName) {

    }

    handleAddChange(event, form) {
        const { value } = event.target;
        event.value = value;
        this.elementChange(event, form.id, form.type)
    }

    elementAddChange(event, id, type) {
        if (id !== 'ignore_me') {
            let element = getFormElementById(this.form, id, true);
            element = SetFormElementValue(event, element, type);

            if (typeof element.attr.onChange !== 'undefined') {
                followUrl(element.attr.onChange, element);
            }
            setFormElement(element, this.form)
            this.setState({
                form: this.form,
                sections: this.state.sections
            })
        }
    }

    handleAddFormChange(event, element, value) {
        if (element.type === 'collection' && element.name === 'parents') {
            if (value.value !== '') {
                if (typeof element.value !== 'object') element.value = [];
                // Check for duplicate choice.
                let ok = true;
                Object.keys(element.value).map(i => {
                    const item = element.value[i];
                    if (Number(value.value) === Number(item.value) || value.value === item.value) ok = false;
                })
                if (!ok) {
                    window.alert(this.translations.alreadyParentCategory)
                    this.setState({
                        form: {...this.form},
                    });
                    return;
                }
                element.value.push(value);
                element.data = element.value;
                this.form = setFormElement(element, this.form);
                let category = {...this.state.category}
                if (element.name === 'parents') {
                    Object.keys(element.value).map(i => {
                        const item = element.value[i];
                        ok = false;
                        Object.keys(category['parents']).map(o => {
                            let parent = category['parents'][o];
                            if (parent.id === Number(item.value)) {
                                ok = true;
                            }
                        })
                        if (!ok) {
                            const parent = {
                                id: Number(item.value),
                                name: item.label,
                                path: "/genealogy/category/" + Number(item.value) + "/modify",
                            };
                            category['parents'][parent.id] = parent;
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
        if (element.type === 'collection' && element.name === 'search') {
            if (element.value === null) {
                element.value = event.target.value;
            } else {
                element.value += event.target.value;
            }
            if (typeof element.value === 'string') {
                element.value = {value: element.value, label: ''}
            }
            if (element.state.userInput !== element.value.value) {
                element.value.value = element.state.userInput;
            }
            if (element.value.value === value.label && value.label !== '') {
                let url = this.form.template.search.action.replace('{category}', value.value);
                window.open(url,  '_self');
            }
            this.form = setFormElement(element, this.form);
            this.setState({
                form: this.form,
                search: {...element},
            });
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

    removeAddParentCategory(section, parent){
        return;
    }

    render() {
        return (
            <Theme>
                <FlexboxContainer>
                    <Border />
                    <Main>
                        <MainContainer>
                            <H3>{this.translations['Category']}: { this.translations['New Category'] }</H3>
                        </MainContainer>
                    </Main>
                    <Sidebar>
                        <SidebarManager
                            category={this.category}
                            translations={this.translations}
                            form={this.state.form}
                            search={this.state.search}
                            functions={this.functions}
                            sections={this.state.sections}
                            messages={this.state.messages}
                            template={this.state.template}
                        />
                    </Sidebar>
                    <Border />
                </FlexboxContainer>
            </Theme>
        );
    }
}

CategoryAddApp.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
}
