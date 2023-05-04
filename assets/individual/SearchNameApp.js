'use strict';
import React, {Component, Fragment} from "react"
import PropTypes from 'prop-types'
import {Column1, Column4, DarkGreenP, Flexbox, FormElement, H4} from "../component/StyledCSS";
import InputRow from "../form/InputRow";
import ChoiceRow from "../form/ChoiceRow";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';
import {buildFormData, updateFormChange} from "../form/FormManager";
import {fetchJson} from "../component/fetchJson";

export default class SearchNameApp extends Component {
    constructor(props) {
        super(props);

        this.translations = props.translations;
        this.state = {
            search: props.search,
            display: true,
        };
        this.handleChange = this.handleChange.bind(this);
        this.handleFormChange = this.handleFormChange.bind(this);
        this.fetchChoices = this.fetchChoices.bind(this);
    }

    handleChange(e, form) {
        const value = e.target.value;
        this.setState(
            {
                search: updateFormChange(value, form, this.state.search),
            }
        );
    }

    handleFormChange(e, form, value) {
        console.log(e, form, value);
    }

    fetchChoices(suggestions, form, section, userInput) {
        let data = buildFormData({}, this.state.search);
        this.setState({
            display: false,
        });
        fetchJson(
            this.state.search.action,
            {method: this.state.search.method, body: JSON.stringify(data)},
            false)
            .then(data => {
                console.log(data);
                this.setState({
                    display: true,
                    search: data.full_search,
                });
            }).catch(error => {
            console.error('Error: ', error)
            this.setState({
                display: true,
            })
        })

    }

    render() {
        if (this.state.display) {
            return (<Fragment>
                <Flexbox>
                    <Column1><H4 className={'centre'}>Quick Name Search</H4></Column1>
                </Flexbox>
                <Flexbox>
                    <Column1><InputRow translations={this.translations} form={this.state.search.children[0]}
                                       handleChange={this.handleChange}/></Column1>
                    <Column1><InputRow translations={this.translations} form={this.state.search.children[1]}
                                       handleChange={this.handleChange}/></Column1>
                </Flexbox>
                <Flexbox>
                    <Column4><ChoiceRow translations={this.translations} form={this.state.search.children[2]}
                                        handleFormChange={this.handleFormChange} fetchChoices={this.fetchChoices}
                                        section={'name_search'}/></Column4>
                    <Column1><FormElement><DarkGreenP className={'centre'} style={{paddingTop: '7px'}}><FontAwesomeIcon
                        icon={solid('magnifying-glass')}
                        onClick={(e) => this.fetchChoices()} size={'2xl'}
                        title={this.translations['Quick Search Names'] + ' ' + this.state.limit}/></DarkGreenP></FormElement></Column1>
                </Flexbox>
            </Fragment>);
        } else {
            return (<Fragment>
                <Flexbox>
                    <Column1><H4 className={'centre'}>Quick Name Search</H4></Column1>
                </Flexbox>
                <Flexbox>
                    <Column1><DarkGreenP className={'centre'}>{this.translations['Loading']}</DarkGreenP></Column1>
                </Flexbox>
            </Fragment>);
        }
    }
}

SearchNameApp.propTypes = {
    search: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired,
}
