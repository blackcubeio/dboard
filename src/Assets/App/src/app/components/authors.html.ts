export default `<template>
    <au-slot if.bind="!authorsView"></au-slot>
    <au-compose if.bind="authorsView" template.bind="authorsView"></au-compose>
</template>`