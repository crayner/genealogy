'use strict';

import React, {Component, Fragment} from 'react'
import PropTypes from 'prop-types'
import {
    Column1,
    DarkGreenA,
    DarkGreenBold,
    DarkGreenCentreP,
    DarkGreenP,
    Flexbox,
    H3,
    MainContainer
} from "../component/StyledCSS";
import {fetchJson} from "../component/fetchJson";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { solid, regular, brands, icon } from '@fortawesome/fontawesome-svg-core/import.macro';


export default class IndividualList extends Component {
    constructor(props) {
        super(props);
        this.id = props.id;
        this.translations = props.translations;
        this.individuals = props.individuals;

        this.state = {
            ...this.extractListDetails(this.individuals)
        };
        this.letter = '';
    }

    extractListKeys(individuals) {
        let keys = {};
        Object.keys(individuals).map(i => {
            const member = individuals[i];
            if (typeof keys[member.anchorKey] === 'undefined') {
                keys[member.anchorKey] = i;
            }
        })
        return keys;
    }
    extractListDetails() {
        let result = {};
        if (this.individuals[0].fetch) {
            let route = '/genealogy/category/{category}/members';
            route = route.replace('{category}', this.id);
            fetchJson(
                route,
                {method: 'GET'},
                false)
                .then(data => {
                    this.individuals = data.members;
                    result['count'] = this.individuals.length;
                    result['keys'] = this.extractListKeys(this.individuals);
                    result['start'] = 0;
                    result['limit'] = 201;
                    this.setState({
                        ...result,
                    });
                }).catch(error => {
                 console.error('Error: ', error)
                })
        }
        if (this.individuals[0].fetch) {
            result['count'] = this.translations['Loading'];
        } else {
            result['count'] = this.individuals.length;
        }
        result['keys'] = this.extractListKeys(this.individuals);
        result['start'] = 0;
        result['limit'] = 201;
        return result;
    }

    renderIndividuals()
    {
        if (this.individuals[0].fetch) return null;

        this.letter = '';

        let members = this.individuals;
        if (this.state.count > this.state.limit) {
            members = this.individuals.slice(this.state.start, this.state.start + this.state.limit);
        }
        const pageCount = members.length;

        let count = Math.ceil(pageCount / 3);

        let lists = [];
        for (let i = 0; i < pageCount; i += count) {
            const chuck = members.slice(i, i + count);
            const result = this.displayMembers(chuck);
            lists.push(<Column1 key={i}>{result}</Column1>);
        }
        return (<Flexbox>{lists}</Flexbox>);
    }

    displayMembers(members) {
        let newColumn = true;
        return Object.keys(members).map(i => {
            const person = members[i];
            var details = '';
            if (person.birthDate !== '') {
                details += ' ' + person.birthDate;
            }
            if (person.birthLocation !== '') {
                details += ' ' + person.birthLocation;
            }
            if (person.deathDate !=='') {
                details += ' - ' + person.deathDate;
            }

            if (this.state.count >= 21) {
                if (newColumn && this.letter === person.anchorKey) {
                    newColumn = false;
                    return (<Fragment key={i}><H3>{this.letter} {this.translations['cont']}.</H3><DarkGreenP key={i}><DarkGreenBold><DarkGreenA href={person.path} target={'_blank'}>{person.name}</DarkGreenA></DarkGreenBold>{details}.</DarkGreenP></Fragment>)
                }
                if (this.letter !== person.anchorKey) {
                    this.letter = person.anchorKey;
                    newColumn = false;
                    return (<Fragment key={i}><H3>{this.letter}</H3><DarkGreenP key={i}><DarkGreenBold><DarkGreenA href={person.path} target={'_blank'}>{person.name}</DarkGreenA></DarkGreenBold>{details}.</DarkGreenP></Fragment>)
                }
            }
            return (<DarkGreenP key={i}><DarkGreenBold><DarkGreenA href={person.path} target={'_blank'}>{person.name}</DarkGreenA></DarkGreenBold>{details}.</DarkGreenP>);
        })

    }

    handleAnchorClick(e, key) {
        let start = 0;
        if (typeof this.state.keys[key] !== 'undefined') start = parseInt(this.state.keys[key])
        if (key === 'previous') {
            start = this.state.start - this.state.limit;
            if (start < 0) start = 0;
        }
        if (key === 'next') {
            start = this.state.start + this.state.limit;
            if (start > this.state.count) start = this.state.count - this.state.limit;
        }
        this.setState({
            start: start,
        });
    }
    renderAnchorKeys() {
        if (this.state.count === this.translations['Loading'] || this.state.count < 21) return null;
        let result = [];
        if (this.state.start > 0) {
            result.push(<Fragment key={'prev'}><FontAwesomeIcon icon={solid('left-long')}
                                         onClick={(e) => this.handleAnchorClick(e, 'previous')}
                                                                title={this.translations['Previous'] + ' ' + this.state.limit} /> </Fragment>)
        }
        Object.keys(this.state.keys).map(i => {
            result.push(<Fragment key={i}><DarkGreenA onClick={(e) => this.handleAnchorClick(e, i)}>{i}</DarkGreenA> </Fragment>);
        });
        if (this.state.start < (this.state.count - this.state.limit)) {
            result.push(<FontAwesomeIcon key={'next'} icon={solid('right-long')}
                                         onClick={(e) => this.handleAnchorClick(e, 'next')}
                                         title={this.translations['Previous'] + ' ' + this.state.limit} />)
        }
        return <DarkGreenCentreP>{result}</DarkGreenCentreP>;
    }
    render() {
        return (
            <MainContainer>
                <H3>{this.translations.IndividualProfiles} ({this.state.count})</H3>
                {this.renderAnchorKeys()}
                {this.renderIndividuals()}
                {this.renderAnchorKeys()}
            </MainContainer>
        );
    }
}

IndividualList.propTypes = {
    individuals: PropTypes.array.isRequired,
    translations: PropTypes.object.isRequired,
    id: PropTypes.number.isRequired,
};