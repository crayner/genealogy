'use strict';

import React, { Component, Fragment } from "react";
import PropTypes from 'prop-types';
import {FormElementInput} from "./InputRow";
import styled from 'styled-components';

const NoSuggestions = styled.div`
    color: #003300;
    padding: 0.5rem;
`
const SuggestionList = styled.ul`
    border: 1px solid #003300;
    border-top-width: 0;
    list-style: none;
    margin-top: 0;
    margin-left: 5px;
    max-height: 150px;
    overflow-y: auto;
    padding-left: 0;
    width: 100%;
`
const Suggestion = styled.li`
    line-height: 17px;
    &:hover {
        background-color: #003300;
        color: white;
        cursor: pointer;
        font-weight: 700;
    }
`
const SuggestionActive = styled.li`
    background-color: #003300;
    color: white;
    cursor: pointer;
    font-weight: 700;
`

export default function Autocomplete(props) {
    const {
        form,
        translations,
        suggestions,
        handleFormChange,
        fetchChoices,
        multiple,
        section,
    } = props;

    function onKeyDown(e, form) {
        const { activeSuggestion, filteredSuggestions } = form.state;

        if (e.keyCode === 13) {
            form.state.activeSuggestion = 0;
            form.state.showSuggestions = false;
            form.state.userInput = filteredSuggestions[activeSuggestion];
        } else if (e.keyCode === 38) {
            if (activeSuggestion === 0) {
                return;
            }
            form.state.activeSuggestion = activeSuggestion - 1;
        }
        // User pressed the down arrow, increment the index
        else if (e.keyCode === 40) {
            if (activeSuggestion - 1 === filteredSuggestions.length) {
                return;
            }
            form.state.activeSuggestion = activeSuggestion + 1;
        }
        let value = {value: e.currentTarget.innerText.toLowerCase(), label: e.currentTarget.innerText}
        handleFormChange(e, form, value);
    }

    function onChange(e) {
        const userInput = e.currentTarget.value;

        let fetchedSuggestions = fetchChoices(suggestions, form, section, userInput);
        const splitInput = userInput.split(' ');

        let filteredSuggestions = fetchedSuggestions.filter(suggestion => {
            if (suggestion.label.toLowerCase().indexOf(userInput.toLowerCase()) > -1) return suggestion;
            let ok = true;
            splitInput.map((testString, i) => {
                if (suggestion.label.toLowerCase().indexOf(testString.toLowerCase()) < 0) ok = false;
            })
            if (ok) return suggestion;
        });

        form.state.activeSuggestion = 0;
        form.state.filteredSuggestions = fetchedSuggestions;
        form.state.showSuggestions = true;
        form.state.userInput = e.currentTarget.value;
        let value = {value: e.currentTarget.innerText.toLowerCase(), label: e.currentTarget.innerText}
        handleFormChange(e, form, value);
    }

    function onClick(e) {
        let value;
        form.state.filteredSuggestions.filter((suggestion,i) => {
            if (e.currentTarget.innerText === suggestion.label) value = form.state.filteredSuggestions[i];
        })

        form.state.activeSuggestion = 0;
        form.state.filteredSuggestions = []
        form.state.showSuggestions = false;
        form.state.userInput = e.currentTarget.innerText;
        handleFormChange(e, form, value);
    }

    function getSuggestionList() {
        let searchList = form.state.userInput.split(' ');
        let i = 0;
        const initialList = Object.keys(form.state.filteredSuggestions).map(index => {
            let suggestion = form.state.filteredSuggestions[index];
            if (suggestion.label.toLowerCase().indexOf(searchList[0].toLowerCase()) === 0) {
                if (index === form.state.activeSuggestion) {
                    return (<SuggestionActive key={i++} onClick={(e) => onClick(e)}>{suggestion.label}</SuggestionActive>);
                }
                return (<Suggestion key={i++} onClick={(e) => onClick(e)}>{suggestion.label}</Suggestion>);
            }
        });
        let remainingList = [];
        Object.keys(form.state.filteredSuggestions).map(index => {
            let suggestion = form.state.filteredSuggestions[index];
            if (suggestion.label.toLowerCase().indexOf(searchList[0].toLowerCase()) !== 0) {
                searchList.map(search => {
                    if (suggestion.label.toLowerCase().indexOf(search.toLowerCase()) > -1) {
                        if (index === form.state.activeSuggestion) {
                            remainingList.push(<SuggestionActive key={i++} onClick={(e) => onClick(e)}>{suggestion.label}</SuggestionActive>);
                        }
                        remainingList.push(<Suggestion key={i++} onClick={(e) => onClick(e)}>{suggestion.label}</Suggestion>);
                    }
                })
            }
        });
        return(
            <SuggestionList>
                {initialList}
                {remainingList}
            </SuggestionList>
        );

    }
    function getSuggestedListComponent() {
        let suggestionsListComponent;
        if (form.state.showSuggestions && form.state.userInput) {
            if (form.state.filteredSuggestions.length > 0) {
                suggestionsListComponent = getSuggestionList();
            } else {
                suggestionsListComponent = (
                    <NoSuggestions>
                        <em>{translations['NoSuggestions']}</em>
                    </NoSuggestions>
                );
            }
        }
        return suggestionsListComponent;
    }

    return (
        <Fragment>
            <FormElementInput type={'text'}
                              id={form.id}
                              name={form.full_name}
                              required={false}
                              value={form.state.userInput}
                              onChange={(e) => onChange(e)}
                              onKeyDown={(e) => onKeyDown(e, form)} />
            {getSuggestedListComponent()}
        </Fragment>
    );
}

Autocomplete.propTypes = {
    translations: PropTypes.object.isRequired,
    form: PropTypes.object.isRequired,
    suggestions: PropTypes.array,
    multiple: PropTypes.bool,
    fetchChoices: PropTypes.func.isRequired,
    handleFormChange: PropTypes.func.isRequired,
    section: PropTypes.string.isRequired,
}
Autocomplete.defaultTypes = {
    suggestions: [],
    multiple: false,
}