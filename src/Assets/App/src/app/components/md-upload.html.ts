export default `<template>
    <au-slot if.bind="!uploadedView"></au-slot>
    <au-compose if.bind="uploadedView" template.bind="uploadedView"></au-compose>
</template>`