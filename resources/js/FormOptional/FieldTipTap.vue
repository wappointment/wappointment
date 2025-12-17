<template>
<div>
    <label v-if="label">{{ label }}</label>
    <editor-menu-bar :editor="editor" v-slot="{ commands, isActive, getMarkAttrs }">
      <div class="menubar">
        <div v-if="commands">
          <div v-if="linkUrl" class="linkfield" :style="styleLinkContainer">
            <input
              type="text"
              @focus="writingUrl=true"
              placeholder="https://"
              v-model="linkUrl"
              @keyup.enter.prevent="confirmLinkMarkNew(commands)"
            >
            <button class="btn btn-secondary btn-xs" @click.prevent="confirmLinkMarkNew(commands)">
              <span class="dashicons dashicons-yes"></span>
            </button>
            <button class="btn btn-secondary btn-xs" @click.prevent="removeLinkMarkNew(commands)">
              <span class="dashicons dashicons-editor-unlink"></span>
            </button>
          </div>
          <div>
            <div class="d-flex flex-wrap">
              <button
                class="btn btn-secondary btn-xs"
                :class="{ 'active': isActive[button.mark] && isActive[button.mark]() }"
                @click.prevent="commands[button.mark] && commands[button.mark]()"
                v-for="button in toolbar"
              >
                <span
                  v-if="button.icon.indexOf('dashicons-') !== -1"
                  class="dashicons"
                  :class="button.icon"
                ></span>
              </button>

              <div class="dropdown" v-if="!simpleVersion">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  type="button"
                  @click="toggleDDP('ddph')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >Size</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddph}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': isActive.paragraph && isActive.paragraph() }"
                    @click.prevent="commands.paragraph && commands.paragraph()"
                  >Normal</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    v-for="n in 3"
                    :class="{ 'active': isActive.heading && isActive.heading({ level: n }) }"
                    @click.prevent="commands.heading && commands.heading({ level: n })"
                  >H{{ n }}</a>
                </div>
              </div>
              <div class="dropdown" :data-tt="get_i18n('dynamicdata', 'settings')">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  type="button"
                  @click="toggleDDP('ddpf')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >{{ get_i18n('fields', 'settings') }}</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddpf}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    v-for="etag in emailtags"
                    @click.prevent="insertCfieldNew(commands, etag.model, etag.key)"
                  >{{ etag.label }}</a>
                </div>
              </div>
              <div class="dropdown" :data-tt="selectionIsOn ? get_i18n('linkselection', 'settings'):get_i18n('selectenable', 'settings')">
                <button
                  class="btn btn-secondary dropdown-toggle"
                  :class="{'disabled':!selectionIsOn}"
                  :disabled="!selectionIsOn"
                  type="button"
                  @click="selectionIsOn ? toggleDDP('ddpl'):false"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >{{ get_i18n('links', 'settings') }}</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddpl}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    v-for="elink in emaillinks"
                    @click.prevent="linkToSomethingNew(commands, elink.model, elink.key)"
                  >{{ elink.label }}</a>
                </div>
              </div>
              <div class="dropdown" :data-tt="get_i18n('showonlywhen', 'settings')">
                <button
                  class="btn btn-secondary dropdown-toggle"
                   :class="{'active': (isActive.cblockphysical && isActive.cblockphysical() ||  isActive.cblockphone && isActive.cblockphone() || isActive.cblockzoom && isActive.cblockzoom())}"
                  type="button"
                  @click="toggleDDP('ddpc')"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >{{ get_i18n('showhen', 'settings') }}</button>
                <div
                  class="dropdown-menu"
                  :class="{'show':ddpc}"
                  aria-labelledby="dropdownMenuButton"
                >
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': isActive.cblockphone && isActive.cblockphone() }"
                    @click.prevent="conditionalBlockNew(commands, 'cblockphone')"
                  >{{ get_i18n('phonesession', 'settings') }}</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': isActive.cblockzoom && isActive.cblockzoom() }"
                    @click.prevent="conditionalBlockNew(commands, 'cblockzoom')"
                  >{{ get_i18n('videosession', 'settings') }}</a>
                  <a
                    class="dropdown-item btn btn-secondary"
                    :class="{ 'active': isActive.cblockphysical && isActive.cblockphysical() }"
                    @click.prevent="conditionalBlockNew(commands, 'cblockphysical')"
                  >{{ get_i18n('physicalsession', 'settings') }}</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </editor-menu-bar>
    <editor-content :editor="editor" ref="editorComponent" />
    <div class="footer-reminder" >
      <div v-if="simpleVersion">
        <div>Total characters:  {{ characterCount }}</div>
        <div v-if="definition.sms">
          <span v-if="characterCount > 160" class="small text-danger">
            {{ characterCount > 306 ? Math.ceil(characterCount/153):2 }} SMS will be sent </span> 
            <div>
              <span v-if="hasShortcodes.length>0" class="small text-muted">shortcodes <span v-for="short in hasShortcodes"> {{ short }}  </span> will increase your character count invariably</span>
            </div>
        </div> 
      </div>
      <div class="p-0 d-flex align-items-center linkcolor" v-else>
        <LinkEdit :fieldValue="definition.save_appointment_text_link" fieldKey="save_appointment_text_link" :color="linkColor"/>
        <div class="text-muted mx-2" v-if="definition.allow_cancellation || definition.allow_rescheduling"> | </div>
        <LinkEdit v-if="definition.allow_rescheduling" :fieldValue="definition.reschedule_link" fieldKey="reschedule_link" :color="linkColor"/>
        <div class="text-muted mx-2" v-if="definition.allow_cancellation && definition.allow_rescheduling"> | </div>
        <LinkEdit v-if="definition.allow_cancellation" :fieldValue="definition.cancellation_link" fieldKey="cancellation_link" :color="linkColor"/>
      </div>
      <FooterEdit :fieldValue="definition.email_footer" fieldKey="email_footer" />
    </div>
    <div class="mt-2">
      <ColorPicker v-if="!simpleVersion" v-model="linkColor" @validated="validatedColor" :label="get_i18n('bwe_primary_color','common')"/>
    </div>
  </div>
</template>

<script>
import AbstractField from '../Form/AbstractField'
import ColorPicker from '../Components/ColorPicker'
import { Editor, EditorContent, EditorMenuBar } from 'tiptap'
import {
  Blockquote,
  CodeBlock,
  HardBreak,
  Heading,
  Image,
  OrderedList,
  BulletList,
  ListItem,
  TodoItem,
  TodoList,
  Bold,
  Code,
  Italic,
  Link,
  Strike,
  Underline,
  History,
  Placeholder
} from 'tiptap-extensions'
import CustomFieldNode from "./text-editor/CustomField.js"
import ConditionalPhoneBlockNode from "./text-editor/ConditionalPhoneBlock.js"
import ConditionalZoomBlockNode from "./text-editor/ConditionalZoomBlock.js"
import ConditionalPhysicalBlockNode from "./text-editor/ConditionalPhysicalBlock.js"
import LinkEdit from "../Components/LinkEdit"
import FooterEdit from "../Components/FooterEdit"
import SettingsSave from '../Modules/SettingsSave'

export default {
  name:'opt-tiptap',
    extends:SettingsSave,
    mixins: [AbstractField],
    components: {
        EditorContent,
        EditorMenuBar,
        LinkEdit,
        FooterEdit,
        ColorPicker
    },
    data(){
        return {
        editor: null,
        linkColor:null,
        position: false,
        linkUrl: null,
        writingUrl: false,
        characterCount:0,
        hasShortcodes: [],
        toolbar: [
            {
            mark: "bold",
            icon: "dashicons-editor-bold"
            },
            {
            mark: "italic",
            icon: "dashicons-editor-italic"
            },
            {
            mark: "link",
            icon: "dashicons-admin-links",
            activeIcon: "dashicons-admin-links"
            },
            {
            mark: "strike",
            icon: "dashicons-editor-strikethrough"
            },
            {
            mark: "underline",
            icon: "dashicons-editor-underline"
            },
            {
            mark: "bullet_list",
            icon: "dashicons-editor-ul"
            },
            {
            mark: "ordered_list",
            icon: "dashicons-editor-ol"
            }
        ],
        ddph: false,
        ddpf: false,
        ddpl: false,
        ddpc: false,
        emaillinks: window.wappoEmailLinks,
        emailtags: window.wappoEmailTags,
        extensions: [],
        };
    },
    computed: {
      selectionIsOn(){
        if(this.editor !== null){
          return this.editor.state.selection.ranges[0].$to.pos - this.editor.state.selection.ranges[0].$from.pos > 0
        }
        return false
      },
      styleLinkContainer() {
        if (this.position) {
          let topPos = Math.round(this.position.top - this.position.height);
          let leftPos = Math.round(32);
          if (this.editor.state.ranges !== undefined) {
            leftPos +=
              this.editor.state.ranges[0].$from.pos -
              this.editor.state.ranges[0].$to.pos;
          }
          let position = "fixed";
          if (this.position.top == 0) {
            topPos = 0;
            leftPos = 0;
            position = "absolute";
            return "";
          }

          return (
            "display:block !important;position:" +
            position +
            ";" +
            "top:" +
            topPos +
            "px;left:" +
            leftPos +
            "px;"
          );
        }
        return "";
      }
  },
  created(){
    this.simpleVersion = this.definition.simple !== undefined && this.definition.simple === true
    
    let extensions = []
    if(this.simpleVersion){
      this.toolbar=[]
      extensions = [
        new CustomFieldNode(),
        new ConditionalPhoneBlockNode(),
        new ConditionalZoomBlockNode(),
        new ConditionalPhysicalBlockNode()
      ]
    } else {
      extensions = [
        new Blockquote(),
        new BulletList(),
        new CodeBlock(),
        new HardBreak(),
        new Heading({ maxLevel: 3 }),
        new Image(),
        new ListItem(),
        new OrderedList(),
        new TodoItem(),
        new TodoList(),
        new Bold(),
        new Code(),
        new Italic(),
        new Link(),
        new Strike(),
        new Underline(),
        new History(),
        new Placeholder(),
        new CustomFieldNode(),
        new ConditionalPhoneBlockNode(),
        new ConditionalZoomBlockNode(),
        new ConditionalPhysicalBlockNode()
      ]
    }
    this.linkColor = this.definition.link_color
    
    // Create the Editor instance
    this.editor = new Editor({
      extensions: extensions,
      content: this.value || '',
      onUpdate: this.updateModel
    })
  },
    methods:{
      validatedColor(){
        this.settingSave('email_link_color', this.linkColor)
      },
      updateModel({ getJSON, getHTML }) {
          this.updatedValue = getJSON()
          this.hasShortcodes = this.getShortcodes(this.editor.state.doc.content)
          this.characterCount = this.editor.state.doc.content.size - 2
      },
      getShortcodes(content){
        let customfields = []
        if(content.content[0] !== undefined && content.content[0].content !== undefined && content.content[0].content.content !== undefined){
          for (let i = 0; i < content.content[0].content.content.length; i++) {
            const element = content.content[0].content.content[i]
            if(element.attrs.class !== undefined && element.attrs.class=='customfield'){
              customfields.push('['+element.attrs.src+':'+element.attrs.alt+']')
            }
            
          }
        }
        return customfields
      },
      getSelectionDimensions() {
        var sel = document.selection,
          range;
        var width = 0,
          height = 0;
        if (window.getSelection) {
          sel = window.getSelection();
          if (sel.rangeCount) {
            range = sel.getRangeAt(0).cloneRange();
            if (range.getBoundingClientRect) {
              var rect = range.getBoundingClientRect();
              width = rect.right - rect.left;
              height = rect.bottom - rect.top;
              return {
                width: width,
                height: height,
                top: rect.top,
                left: rect.left,
                right: rect.right,
                bottom: rect.bottom
              };
            }
          }
        }
        return { width: 0, height: 0, top: 0, left: 0, right: 0, bottom: 0 };
      },

      showLinkInput(marks, nodes) {
        if (!this.writingUrl) {
          this.setLink(
            marks["link"].attrs.href !== undefined ? marks["link"].attrs.href : ""
          );
        }
      },
      getActiveState(marks, nodes, mark) {
        if (marks[mark] !== undefined) {
          if (mark == "link") {
            if (marks[mark].active()) {
              if (!this.writingUrl && marks[mark].attrs.href != this.linkUrl) {
                this.resetLink();
              }
              if (this.styleLinkContainer == "") {
                this.showLinkInput(marks, nodes);
              }
            } else {
              this.resetLink();
            }
          }

          return marks[mark].active();
        }
        if (nodes[mark] !== undefined) {
          return nodes[mark].active();
        }
      },

      confirmLinkMark(marks, nodes) {
        let attrs = { href: this.linkUrl };
        marks["link"].command(attrs);
        this.resetLink();
        this.writingUrl = false;
      },

      removeLinkMark(marks, nodes) {
        let attrs = { href: "" };
        marks["link"].command(attrs);
        this.resetLink();
        this.writingUrl = false;
      },

      setLink(linkvalue = "") {
        this.linkUrl = linkvalue;
        this.position = this.getSelectionDimensions();
      },

      resetLink() {
        this.linkUrl = "";
        this.position = false;
      },

      getCommand(marks, nodes, mark) {
        if (marks[mark] !== undefined) {
          if (mark == "link") {
            marks["link"].command({ href: "http://" });
          } else {
            return marks[mark].command();
          }
        }
        if (nodes[mark] !== undefined) {
          return nodes[mark].command();
        }
      },
      setContent() {
        // set content for json object
        this.editor.setContent(
          JSON.parse(JSON.stringify(this.value)),
          true
        );

        this.editor.focus();
      },

      toggleDDP(ddpName) {

        let newVal = true
        if (this[ddpName] == true) newVal = false
        this.hideDropDowns()
        this[ddpName] = newVal
      },

      hideDropDowns() {
        this.ddph = false
        this.ddpf = false
        this.ddpc = false
        this.ddpl = false
      },

      insertCfield(nodes, model, key) {
        this.hideDropDowns();
        return nodes.customfield.command({ src: model, alt: key })
      },

      linkToSomething(marks,  model, key) {
        this.hideDropDowns()
        marks["link"].command({ href: "["+model+':'+key+"]" })
        this.resetLink();
        this.writingUrl = false;
      },

      wrapHeaders(nodes, n = false) {
        if (n === false) nodes.paragraph.command();
        else nodes.heading.command({ level: n });
        this.hideDropDowns();
      },

      conditionalBlock(nodes, activateCondition) {
        let cblocks = ["cblockphone", "cblockphysical", "cblockzoom"];
        let rerun = true;
        for (let index = 0; index < cblocks.length; index++) {
          const condition = cblocks[index];
          if (nodes[condition].active()) {
            nodes[condition].command();
            if (activateCondition == condition) rerun = false;
          }
        }

        if (rerun) nodes[activateCondition].command();
        this.hideDropDowns();
      },

      // New methods using commands API
      insertCfieldNew(commands, model, key) {
        this.hideDropDowns();
        if (commands.customfield) {
          return commands.customfield({ src: model, alt: key });
        }
      },

      linkToSomethingNew(commands, model, key) {
        this.hideDropDowns();
        if (commands.link) {
          commands.link({ href: "["+model+':'+key+"]" });
        }
        this.resetLink();
        this.writingUrl = false;
      },

      conditionalBlockNew(commands, activateCondition) {
        if (commands[activateCondition]) {
          commands[activateCondition]();
        }
        this.hideDropDowns();
      },

      confirmLinkMarkNew(commands) {
        if (commands.link) {
          commands.link({ href: this.linkUrl });
        }
        this.resetLink();
        this.writingUrl = false;
      },

      removeLinkMarkNew(commands) {
        if (commands.link) {
          commands.link({ href: "" });
        }
        this.resetLink();
        this.writingUrl = false;
      }
    },
    mounted() {
      this.setContent();
    },
    beforeDestroy() {
      if (this.editor) {
        this.editor.destroy();
      }
    },
}
</script>
<style>
.dropdown[data-tt]{
  z-index: 9999;
}
.is-active {
  background-color: #ccc !important;
}
.ProseMirror p {
  margin-bottom: 0.4rem;
}
.conditional {
  border: 2px dashed #eaeaea;
  padding: 0;
  position: relative;
  transition: all 0.3s ease-in;
  margin: 0.2rem 0 !important;
}
.conditional:hover {
  box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.3);
  border-color: #96ccce;
}
.conditional::before {
  border-radius: 2px;
  border: 2px dashed #ccc;
  opacity: 0;
  margin-top: -23px;
  margin-left: -2px;
  width: auto;
  height: auto;
  position: absolute;
  font-family: dashicons;
  color: #898989;
  transition: all 0.3s ease-in;
}

.conditional.conditional-phone::before {
  content: "\f525";
}
.conditional.conditional-physical::before {
  content: "\f231";
}
.conditional.conditional-zoom::before {
  content: "\f235";
}

.conditional:hover::before {
  position: absolute;
  opacity: 1;
  background-color: #effeff;
  padding: 0 5px;
  border-color: #96ccce;
}

.conditional.conditional-phone:hover::before {
  content: "\f525 " attr(data-tt);
}
.conditional.conditional-physical:hover::before {
  content: "\f231 " attr(data-tt);
}
.conditional.conditional-zoom:hover::before {
  content: "\f235 " attr(data-tt);
}

.ProseMirror {
  box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.3);
  border: 1px solid #ccc;
  padding: 0.4rem;
}

.menubar {
  padding: 0.4rem;
  background-color: #d7d7d7;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
  position: relative;
}


.ProseMirror ul {
  list-style: disc;
  margin-left: 1.8rem;
}

.menubar .linkfield {
    padding: 0.5rem;
    background-color: #969696;
    border-radius: 0.2rem;
    display: none;
    position: absolute !important;
    top: 52px !important;
    left: 0 !important;
}

.ProseMirror-focused a {
  -webkit-user-select: all;
  user-select: all;
}
.footer-reminder{
  padding: 1rem;
  border: dashed 2px #ccc;
  text-align: center;
}
</style>

