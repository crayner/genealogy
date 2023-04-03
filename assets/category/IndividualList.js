'use strict';

import React from 'react'
import PropTypes from 'prop-types'
import {Column1, DarkGreenA, DarkGreenBold, DarkGreenP, Flexbox, H3, MainContainer} from "../component/StyledCSS";

export default function IndividualList(props) {
    const {
        individuals,
        translations,
    } = props;

    function renderIndividuals()
    {
        var count = Math.ceil(individuals.length / 3);

        var result = individuals.map((person, i) => {
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

            return (<DarkGreenP key={i}><DarkGreenBold><DarkGreenA href={person.path} target={'_blank'}>{person.name}</DarkGreenA></DarkGreenBold>{details}.</DarkGreenP>);
        })

        var lists = [];
        for (let i = 0; i < result.length; i += count) {
            const chuck = result.slice(i, i + count);
            lists.push(<Column1 key={i}>{chuck}</Column1>);
        }
        return (<Flexbox>{lists}</Flexbox>);
    }



    return (
        <MainContainer>
            <H3>{translations.IndividualProfiles}</H3>
            {renderIndividuals()}
        </MainContainer>
    );
}

IndividualList.propTypes = {
    individuals: PropTypes.array.isRequired,
    translations: PropTypes.object.isRequired,
};