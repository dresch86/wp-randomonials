<?php
$randomonial_template = <<<'JSON'
{
    "fields":
    {
        "core": 
        {
            "comment":
            {
                "type":"div",
                "class":"randomonial-comment",
                "attributes":[]
            },
            "author":
            {
                "type":"div",
                "class":"randomonial-author",
                "attributes":[]
            }
        },
        "custom":
        {
            "location":
            {
                "type":"div",
                "class":"randomonial-location",
                "attributes":[]
            }
        }
    },
    "tagStack":["core:comment", "core:author", "custom:location"]
}
JSON;
