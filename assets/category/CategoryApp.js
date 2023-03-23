'use strict';
import React, { Component } from "react"
import PropTypes from 'prop-types'
import IndividualList from "./IndividualList";

export default class CategoryApp extends Component {
    constructor(props) {
        super(props);

        this.state  = {
            category: props.category
        };
        this.translations = props.translations;

        this.renderParents = this.renderParents.bind(this);
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

    render() {
        return (
            <div className="flexbox-container">
                <div className="border"></div>
                <div className="main">
                    <div className="main-container">
                    <h3>{this.translations['Category']}: { this.state.category.name }</h3>
                        {this.renderParents(this.state.category.parents)}
                    </div>
                    <IndividualList
                        translations={this.translations}
                        individuals={this.state.category.individuals}
                    />
                </div>
                <div className="sidebar">
                    <h4 className="centre">Genealogy Base Template (Sidebar)</h4>
                </div>
                <div className="border"></div>
            </div>
        );
    }
}

CategoryApp.propTypes = {
    category: PropTypes.object.isRequired,
    translations: PropTypes.object.isRequired
}
