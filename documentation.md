# The syntax 
The syntax of the editor is quite similar to html, but it's not exactely the same. It only supports a small subset of 
html listed in this documentation.

Currently, the parser is relatively
strict, and for example you can't have spaces in the tags where there doesn't need to be one. E.g. `<a></a>` is valid,
but `< a ></ a>` isn't.

## Links
`<a href="http://example.com">Click me</a>`

`<a href="http://example.com" newtab>Click me</a>` Opens it in a new tab

`<a href="seatinfo:article/id">Click me</a>` Opens the article with id. You can find the whole url in the managment tab.

`<a href="seatinfo:resource/id">Click me</a>` Opens a resource uploaded to seat


## Links within the page
You can create links within the article to for example have a table of contents full of links that jump you to the right 
section. To create such a link, add this to jump destination: `<pagelink id="your_id">`. To actually create the link to 
the target, use a normal link like this: `<a href="#your_id">jump down</a>`.

You aren't restricted to the `pagelink` element anymore, `id` should work on any element. As of right now, it only 
remains as a compatibility element and will be removed in future versions.

## Bold
`<b>bold text</b>`


## Content separation
`<hr>` Same as the html element


## Newline
`<br>`

It's also possible to make a newline by pressing enter


## Titles and Subtitles
`<h1>Main title</h1>`

`<h2>Smaller title</h2>`

`<h3>Even smaller title</h3>`

`<h4>4th subtitle title</h4>`

`<h5>5th subtitle title</h5>`

`<h6>Tiny title</h6>`


## Ordered Lists
```
<ol>
    <li>list entry</li>
    <li>list entry</li>
    <li>list entry</li>
</ol>
```


## Unordered Lists
```
<ul>
    <li>list entry</li>
    <li>list entry</li>
    <li>list entry</li>
</ul>
```


## Paragraph
`<p>text</p>`

## Italic
`<i>text</i>`

## Crossed Text
`<s>crossed text</s>`

## Tables
```
<table stripes border>
    <thead>
        <tr>
            <th>Column1</th>
            <th>Column2</th>
            <th>Column3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
        </tr>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
        </tr>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
        </tr>
    </tbody>
</table>
```
Add the argument `stripes` and `borders` to the table to add stripes and borders

## Images
`<img src="link-to-image.png" alt="image description">`

You can specify a file uploaded to resources: `<img src="seatinfo:resource/id" alt="image description">`
To get the id, go to the management page.
